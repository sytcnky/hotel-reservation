<?php

namespace App\Http\Controllers;

use App\Jobs\SendOrderCreatedEmails;
use App\Models\CheckoutSession;
use App\Models\PaymentAttempt;
use App\Services\CheckoutSessionGuard;
use App\Services\CheckoutStartService;
use App\Services\OrderFinalizeService;
use App\Services\PaymentAttemptService;
use App\Services\PaymentGateway3dsInterface;
use App\Services\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    /**
     * Ödeme sayfası (CheckoutSession.code ile)
     */
    public function show(
        string $code,
        Request $request,
        CheckoutSessionGuard $guard,
        PaymentAttemptService $attempts
    ) {
        $session = $guard->loadByCode($code);

        $guard->authorize($request, $session, requireSignatureForGuest: true);

        // completed → success
        if ($session->status === CheckoutSession::STATUS_COMPLETED) {
            return redirect()->to(localized_route('success'));
        }

        // TTL → expired olarak işaretle (read tarafında deterministik)
        if ($guard->expireIfNeededAndFinalizeAttempt($session, 'expired_read:' . $session->id, $attempts)) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'payment_session_expired');
        }

        $payable = max(
            (float) $session->cart_total - (float) $session->discount_amount,
            0
        );

        $attemptError = $attempts->latestAttemptErrorForSession($session);

        // Form idempotency key (her POST için). Pending unique index zaten 2 attempt’ı engeller.
        $submitNonce = bin2hex(random_bytes(16));

        return view('pages.payment.index', [
            'order'        => null,
            'draft'        => null,
            'draftCode'    => null,
            'checkout'     => $session,
            'submitNonce'  => $submitNonce,
            'attemptError' => $attemptError,
            'totalAmount'  => $payable,
            'currency'     => $session->currency,
        ]);
    }

    /**
     * 3D Secure demo sayfası
     */
    public function show3ds(
        string $code,
        Request $request,
        CheckoutSessionGuard $guard,
        PaymentAttemptService $attempts
    ) {
        $session = $guard->loadByCode($code);

        // Guest için: signed varsa geçerli olmalı; signature yoksa (ör. eski view) token zaten 2. faktör olarak çalışıyor.
        $guard->authorize($request, $session, requireSignatureForGuest: false);

        if ($session->status === CheckoutSession::STATUS_COMPLETED) {
            return redirect()->to(localized_route('success'));
        }

        if ($guard->isExpired($session) || $session->status === CheckoutSession::STATUS_EXPIRED) {
            // Session expired ise: status + attempt finalize (idempotent)
            $guard->finalizeAttemptIfSessionExpired($session, 'expired_read:' . $session->id, $attempts)
            || $guard->expireIfNeededAndFinalizeAttempt($session, 'expired_read:' . $session->id, $attempts);

            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'payment_session_expired');
        }

        $token = (string) $request->query('token', '');
        $attemptId = (int) $request->query('attempt', 0);

        if ($token === '' || $attemptId <= 0) {
            return redirect()
                ->to($this->paymentUrl($session))
                ->with('err', 'payment_3ds_missing_params');
        }

        $attempt = $attempts->findPending3dsAttemptForSession($session, $attemptId);

        if (! $attempt || $attempt->status !== PaymentAttempt::STATUS_PENDING_3DS) {
            return redirect()
                ->to($this->paymentUrl($session))
                ->with('err', 'payment_3ds_invalid_attempt');
        }

        if (! $attempts->isValid3dsToken($attempt, $token)) {
            return redirect()
                ->to($this->paymentUrl($session))
                ->with('err', 'payment_3ds_invalid_token');
        }

        return view('pages.payment.3ds-demo', [
            'checkout'  => $session,
            'attemptId' => $attempt->id,
            'token'     => $token,
        ]);
    }

    /**
     * 3D Secure sonucu (demo simülasyon) - success/fail
     */
    public function complete3ds(
        string $code,
        Request $request,
        CheckoutSessionGuard $guard,
        PaymentAttemptService $attempts,
        OrderFinalizeService $finalize
    ) {
        $validated = $request->validate([
            'attempt_id' => ['required', 'integer', 'min:1'],
            'token'      => ['required', 'string', 'min:10'],
            'result'     => ['required', 'in:success,fail'],
        ]);

        $checkout = $guard->loadByCode($code);

        // Guest için: signed varsa geçerli olmalı; signature yoksa token doğrulaması zaten var (akış bozulmasın).
        $guard->authorize($request, $checkout, requireSignatureForGuest: false);

        if ($checkout->status === CheckoutSession::STATUS_COMPLETED) {
            return redirect()->to(localized_route('success'));
        }

        if ($guard->isExpired($checkout) || $checkout->status === CheckoutSession::STATUS_EXPIRED) {
            // Session expired ise: status + attempt finalize (idempotent)
            $guard->finalizeAttemptIfSessionExpired($checkout, 'expired_read:' . $checkout->id, $attempts)
            || $guard->expireIfNeededAndFinalizeAttempt($checkout, 'expired_read:' . $checkout->id, $attempts);

            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'payment_session_expired');
        }

        $attempt = $attempts->getAttemptForComplete3dsOrFail($checkout, (int) $validated['attempt_id']);

        if ($attempt->status !== PaymentAttempt::STATUS_PENDING_3DS) {
            return redirect()
                ->to($this->paymentUrl($checkout))
                ->with('err', 'payment_3ds_invalid_attempt');
        }

        if (! $attempts->isValid3dsToken($attempt, (string) $validated['token'])) {
            return redirect()
                ->to($this->paymentUrl($checkout))
                ->with('err', 'payment_3ds_invalid_token');
        }

        $gateway = PaymentGatewayFactory::make();

        if (! $gateway instanceof PaymentGateway3dsInterface) {
            return redirect()
                ->to($this->paymentUrl($checkout))
                ->with('err', 'payment_3ds_not_supported');
        }

        $result = $gateway->complete3ds($checkout, $attempt, [
            'result' => (string) $validated['result'],
        ]);

        // 3DS FAIL → attempt failed + payment’e dön
        if (empty($result['success'])) {
            $attempt->forceFill([
                'status'            => PaymentAttempt::STATUS_FAILED,
                'gateway_reference' => $result['gateway_reference'] ?? $attempt->gateway_reference,
                'error_code'        => $result['error_code'] ?? ($result['code'] ?? null) ?? '3DS_FAILED',
                'error_message'     => $result['message'] ?? '3D Secure doğrulaması başarısız.',
                'raw_request'       => $result['raw_request'] ?? null,
                'raw_response'      => $result['raw_response'] ?? $result,
                'completed_at'      => now(),
            ])->save();

            return redirect()
                ->to($this->paymentUrl($checkout));
        }

        try {
            [$order, $orderCreatedNow] = $finalize->finalizeSuccess($checkout, $attempt, $result);
        } catch (\Throwable $e) {
            logger()->error('payment_finalize_failed', [
                'checkout_id' => $checkout->id ?? null,
                'checkout_code' => $checkout->code ?? null,
                'attempt_id' => $attempt->id ?? null,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'payment_finalize_failed');
        }

        session()->forget('cart');
        session()->forget('cart.applied_coupons');

        // mail dispatch (idempotent)
        if ($order && $orderCreatedNow) {
            SendOrderCreatedEmails::dispatch((int) $order->id);
        }

        return redirect()->to(localized_route('success'));
    }

    public function start(
        Request $request,
        CheckoutStartService $checkoutStart
    ) {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login', [
                'from_cart' => 1,
                'redirect'  => localized_route('cart'),
                'guest'     => 0,
            ]);
        }

        $cart = session('cart');
        if (! $cart || empty($cart['items'])) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_cart_empty');
        }

        $items = (array) ($cart['items'] ?? []);

        $r = $checkoutStart->startForUser($user, $request, $items);

        if (empty($r['ok'])) {
            $err = (string) ($r['err'] ?? 'err_cart_empty');

            return redirect()
                ->to(localized_route('cart'))
                ->with('err', $err);
        }

        /** @var CheckoutSession $checkout */
        $checkout = $r['checkout'];

        return redirect()->to(localized_route('payment', ['code' => $checkout->code]));
    }

    public function startGuest(
        Request $request,
        CheckoutStartService $checkoutStart
    ) {
        $guest = $request->validate([
            'guest_first_name' => ['required', 'string', 'min:2'],
            'guest_last_name'  => ['required', 'string', 'min:2'],
            'guest_email'      => ['required', 'email'],
            'guest_phone'      => ['required', 'string', 'min:5'],
        ]);

        $cart = session('cart');
        if (! $cart || empty($cart['items'])) {
            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'err_cart_empty');
        }

        $items = (array) ($cart['items'] ?? []);

        $r = $checkoutStart->startForGuest($guest, $request, $items);

        if (empty($r['ok'])) {
            $err = (string) ($r['err'] ?? 'err_cart_empty');

            return redirect()
                ->to(localized_route('cart'))
                ->with('err', $err);
        }

        /** @var CheckoutSession $checkout */
        $checkout = $r['checkout'];

        // Guest ödeme erişimi: code + signature
        $url = $this->signedRoute('payment', ['code' => $checkout->code]);

        \Log::info('guest payment signed url', [
            'route_name' => app()->getLocale() . '.payment',
            'signed_url' => $url,
        ]);

        return redirect()->to($url);
    }

    public function process(
        string $code,
        Request $request,
        CheckoutSessionGuard $guard,
        PaymentAttemptService $attempts
    ) {
        $validated = $request->validate([
            'submit_nonce' => ['required', 'string', 'min:16'],
            'cardholder'   => ['required', 'string', 'min:3'],
            'cardnumber'   => ['required', 'string'],
            'exp-month'    => ['required', 'digits:2'],
            'exp-year'     => ['required', 'digits:2'],
            'cvc'          => ['required', 'digits_between:3,4'],
            'terms'        => ['accepted'],
        ]);

        $checkout = $guard->loadByCode($code);

        $guard->authorize($request, $checkout, requireSignatureForGuest: true);

        if ($checkout->status === CheckoutSession::STATUS_COMPLETED) {
            return redirect()->to(localized_route('success'));
        }

        if ($guard->isExpired($checkout) || $checkout->status === CheckoutSession::STATUS_EXPIRED) {
            // Session expired ise: status + attempt finalize (idempotent)
            $guard->finalizeAttemptIfSessionExpired($checkout, (string) $validated['submit_nonce'], $attempts)
            || $guard->expireIfNeededAndFinalizeAttempt($checkout, (string) $validated['submit_nonce'], $attempts);

            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'payment_session_expired');
        }

        $gateway = PaymentGatewayFactory::make();

        if (! $gateway instanceof PaymentGateway3dsInterface) {
            return redirect()
                ->to($this->paymentUrl($checkout))
                ->with('err', 'payment_3ds_not_supported');
        }

        $cardData = [
            'cardholder'   => $validated['cardholder'],
            'cardnumber'   => $validated['cardnumber'],
            'exp_month'    => $validated['exp-month'],
            'exp_year'     => $validated['exp-year'],
            'cvc'          => $validated['cvc'],
        ];

        $attempt = $attempts->getOrCreatePendingAttempt($checkout, (string) $validated['submit_nonce'], $request);

        $payable = max((float) $checkout->cart_total - (float) $checkout->discount_amount, 0);

        if ((float) $attempt->amount !== (float) $payable || strtoupper((string) $attempt->currency) !== strtoupper((string) $checkout->currency)) {
            $attempt->forceFill([
                'amount'   => $payable,
                'currency' => $checkout->currency,
            ])->save();
        }

        $start = $gateway->start3ds($checkout, $attempt, $cardData);

        if (empty($start['success'])) {
            $attempt->forceFill([
                'status'            => PaymentAttempt::STATUS_FAILED,
                'gateway_reference' => $start['gateway_reference'] ?? null,
                'error_code'        => $start['error_code'] ?? ($start['code'] ?? null) ?? '3DS_START_FAILED',
                'error_message'     => $start['message'] ?? '3D Secure başlatılamadı.',
                'raw_request'       => $start['raw_request'] ?? null,
                'raw_response'      => $start['raw_response'] ?? $start,
                'completed_at'      => now(),
            ])->save();

            return redirect()
                ->to($this->paymentUrl($checkout));
        }

        $attempt->forceFill([
            'status'            => PaymentAttempt::STATUS_PENDING_3DS,
            'gateway_reference' => $start['gateway_reference'] ?? $attempt->gateway_reference,
            'raw_request'       => $start['raw_request'] ?? null,
            'raw_response'      => $start['raw_response'] ?? null,
        ])->save();

        $token = hash_hmac('sha256', (string) $attempt->id, (string) $attempt->idempotency_key);

        // 3DS URL'yi signed üret (attempt/token query ile)
        $url = $this->signedRoute('payment.3ds', [
            'code'    => $checkout->code,
            'attempt' => $attempt->id,
            'token'   => $token,
        ]);

        return redirect()->to($url);
    }

    /**
     * Guest için signed URL üretimi.
     * LocalizedRoute route isimleri {locale}.{baseName} olduğu için burada locale prefix'li isim kullanılır.
     */
    private function signedRoute(string $baseName, array $params = []): string
    {
        $name = app()->getLocale() . '.' . $baseName;

        return URL::signedRoute($name, $params);
    }

    /**
     * Payment sayfasına dönüş: session type'a göre doğru URL (guest -> signed).
     */
    private function paymentUrl(CheckoutSession $session): string
    {
        if ($session->type === CheckoutSession::TYPE_GUEST) {
            return $this->signedRoute('payment', ['code' => $session->code]);
        }

        return localized_route('payment', ['code' => $session->code]);
    }
}
