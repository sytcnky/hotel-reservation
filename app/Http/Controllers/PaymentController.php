<?php

namespace App\Http\Controllers;

use App\Jobs\SendOrderCreatedEmails;
use App\Models\CheckoutSession;
use App\Models\PaymentAttempt;
use App\Services\CheckoutSessionGuard;
use App\Services\CheckoutStartService;
use App\Services\OrderFinalizeService;
use App\Services\PaymentAttemptService;
use App\Services\PaymentGatewayFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    /**
     * Tek notice standardı (LEVEL_ZORUNLU)
     *
     * @param string $toUrl
     * @param 'err'|'warn'|'notice'|'ok' $level
     * @param string $code
     * @param array $params
     */
    private function redirectNotice(string $toUrl, string $level, string $code, array $params = []): RedirectResponse
    {
        $level = in_array($level, ['err', 'warn', 'notice', 'ok'], true) ? $level : 'notice';

        $finalCode = is_string($code) ? trim($code) : '';
        if ($finalCode === '') {
            return redirect()->to($toUrl);
        }

        return redirect()
            ->to($toUrl)
            ->with('notices', [[
                'level'  => $level,
                'code'   => $finalCode,
                'params' => is_array($params) ? $params : [],
            ]]);
    }

    public function show(
        string $code,
        Request $request,
        CheckoutSessionGuard $guard,
        PaymentAttemptService $attempts,
        OrderFinalizeService $finalize
    ) {
        $session = $guard->loadByCode($code);

        /**
         * Banka dönüşünde (ok/fail) bazı ortamlarda session/auth taşıması edge-case olabilir.
         * Kontrat: IDOR bariyeri korunur; ancak bank_return=1 ise 404 yerine login’e yönlendir.
         */
        if (
            $session->type === CheckoutSession::TYPE_USER
            && ! auth()->id()
            && (string) $request->query('bank_return') === '1'
        ) {
            return redirect()->route('login', [
                'from_cart' => 0,
                'redirect'  => localized_route('payment', ['code' => $session->code]),
                'guest'     => 0,
            ]);
        }

        $guard->authorize($request, $session, requireSignatureForGuest: true);

        if ($session->status === CheckoutSession::STATUS_COMPLETED) {
            return redirect()->to(localized_route('success'));
        }

        if ($guard->expireIfNeededAndFinalizeAttempt($session, 'expired_read:' . $session->id, $attempts)) {
            return $this->redirectNotice(
                localized_route('cart'),
                'err',
                'msg.err.payment.session_expired'
            );
        }

        $payable = max(
            (float) $session->cart_total - (float) $session->discount_amount,
            0
        );

        if ($payable <= 0) {
            $nonce = 'free_zero:' . (string) $session->id;

            $attempt = $attempts->getOrCreatePendingAttempt($session, $nonce, $request);

            $attempt->forceFill([
                'amount'            => 0.0,
                'currency'          => $session->currency,
                'status'            => PaymentAttempt::STATUS_SUCCESS,
                'gateway'           => $attempt->gateway ?: 'free',
                'gateway_reference' => $attempt->gateway_reference ?: ('FREE-' . (string) $attempt->id),
                'error_code'        => null,
                'error_message'     => null,
                'raw_request'       => $this->safeRaw(['free' => true, 'reason' => 'zero_payable']),
                'raw_response'      => $this->safeRaw(['free' => true, 'result' => 'approved']),
                'started_at'        => $attempt->started_at ?: now(),
                'completed_at'      => now(),
            ])->save();

            try {
                [$order, $orderCreatedNow] = $finalize->finalizeSuccess($session, $attempt, [
                    'success'           => true,
                    'gateway_reference' => $attempt->gateway_reference,
                    'raw_response'      => ['free' => true, 'result' => 'approved'],
                ]);
            } catch (\Throwable $e) {
                logger()->error('payment_finalize_failed_zero_payable', [
                    'checkout_id'   => $session->id ?? null,
                    'checkout_code' => $session->code ?? null,
                    'attempt_id'    => $attempt->id ?? null,
                    'message'       => $e->getMessage(),
                    'file'          => $e->getFile(),
                    'line'          => $e->getLine(),
                ]);

                return $this->redirectNotice(
                    localized_route('cart'),
                    'err',
                    'msg.err.payment.finalize_failed'
                );
            }

            session()->forget('cart');
            session()->forget('cart.applied_coupons');

            if ($order && $orderCreatedNow) {
                SendOrderCreatedEmails::dispatch((int) $order->id);
            }

            return redirect()->to(localized_route('success'));
        }

        $attempt = $attempts->getOrCreatePendingAttempt(
            $session,
            'hosted_init:' . (string) $session->id . ':' . bin2hex(random_bytes(8)),
            $request
        );

        try {
            $gateway = PaymentGatewayFactory::make();

            $r = $gateway->initiateHostedPayment($session, $attempt, $request);

            if (! empty($r['success']) && ! empty($r['endpoint']) && ! empty($r['params'])) {
                $attempt->forceFill([
                    'raw_request' => $this->safeRaw([
                        'source'   => 'initiate',
                        'endpoint' => (string) $r['endpoint'],
                        'params'   => (array) $r['params'],
                    ]),
                ])->save();

                return view('pages.payment.redirect', [
                    'checkout'     => $session,
                    'endpoint'     => (string) $r['endpoint'],
                    'params'       => (array) $r['params'],
                    'currency'     => $session->currency,
                    'totalAmount'  => $payable,
                ]);
            }

            $attempt->forceFill([
                'status'        => PaymentAttempt::STATUS_FAILED,
                'error_code'    => (string) ($r['error_code'] ?? 'INIT_FAILED'),
                'error_message' => (string) ($r['message'] ?? 'msg.err.payment.gateway_init_failed'),
                'raw_response'  => $this->safeRaw(['source' => 'initiate', 'result' => $r]),
                'completed_at'  => now(),
            ])->save();

        } catch (\Throwable $e) {
            logger()->error('payment_initiate_exception', [
                'checkout_id'   => $session->id ?? null,
                'checkout_code' => $session->code ?? null,
                'attempt_id'    => $attempt->id ?? null,
                'message'       => $e->getMessage(),
                'file'          => $e->getFile(),
                'line'          => $e->getLine(),
            ]);

            $attempt->forceFill([
                'status'        => PaymentAttempt::STATUS_FAILED,
                'error_code'    => 'INIT_EXCEPTION',
                'error_message' => 'msg.err.payment.gateway_init_failed',
                'raw_response'  => $this->safeRaw(['source' => 'initiate', 'exception' => $e->getMessage()]),
                'completed_at'  => now(),
            ])->save();
        }

        $attemptError = $attempts->latestAttemptErrorForSession($session);

        return view('pages.payment.index', [
            'order'        => null,
            'draft'        => null,
            'draftCode'    => null,
            'checkout'     => $session,
            'attemptError' => $attemptError,
            'totalAmount'  => $payable,
            'currency'     => $session->currency,
        ]);
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
            return $this->redirectNotice(
                localized_route('cart'),
                'err',
                'msg.err.cart.empty'
            );
        }

        $items = (array) ($cart['items'] ?? []);

        $r = $checkoutStart->startForUser($user, $request, $items);

        if (empty($r['ok'])) {
            $code = (string) ($r['err'] ?? 'msg.err.cart.empty');

            return $this->redirectNotice(
                localized_route('cart'),
                'err',
                $code
            );
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
            return $this->redirectNotice(
                localized_route('cart'),
                'err',
                'msg.err.cart.empty'
            );
        }

        $items = (array) ($cart['items'] ?? []);

        $r = $checkoutStart->startForGuest($guest, $request, $items);

        if (empty($r['ok'])) {
            $code = (string) ($r['err'] ?? 'msg.err.cart.empty');

            return $this->redirectNotice(
                localized_route('cart'),
                'err',
                $code
            );
        }

        /** @var CheckoutSession $checkout */
        $checkout = $r['checkout'];

        $url = $this->signedRoute('payment', ['code' => $checkout->code]);

        \Log::info('guest payment signed route generated', [
            'route_name' => app()->getLocale() . '.payment',
        ]);

        return redirect()->to($url);
    }

    private function signedRoute(string $baseName, array $params = []): string
    {
        $name = app()->getLocale() . '.' . $baseName;

        $ttlMinutes = (int) config('icr.payments.order_ttl', 1);
        if ($ttlMinutes < 1) {
            $ttlMinutes = 1;
        }

        $expiresAt = now()->addMinutes($ttlMinutes + 2);

        return URL::temporarySignedRoute($name, $expiresAt, $params);
    }

    private function safeRaw(?array $raw): ?array
    {
        if (app()->isProduction()) {
            return null;
        }

        return is_array($raw) ? $raw : null;
    }

    private function paymentUrl(CheckoutSession $session): string
    {
        if ($session->type === CheckoutSession::TYPE_GUEST) {
            return $this->signedRoute('payment', ['code' => $session->code]);
        }

        return localized_route('payment', ['code' => $session->code]);
    }
}
