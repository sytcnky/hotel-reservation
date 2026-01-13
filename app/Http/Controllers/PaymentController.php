<?php

namespace App\Http\Controllers;

use App\Jobs\SendOrderCreatedEmails;
use App\Models\CheckoutSession;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\UserCoupon;
use App\Services\CampaignViewModelService;
use App\Services\CouponViewModelService;
use App\Services\PaymentGateway3dsInterface;
use App\Services\PaymentGatewayFactory;
use App\Support\Helpers\CurrencyHelper;
use App\Support\Helpers\LocaleHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Ödeme sayfası (CheckoutSession.code ile)
     */
    public function show(string $code)
    {
        $session = CheckoutSession::query()
            ->where('code', $code)
            ->firstOrFail();

        // completed → success
        if ($session->status === CheckoutSession::STATUS_COMPLETED) {
            return redirect()->to(localized_route('success'));
        }

        // TTL → expired olarak işaretle (read tarafında deterministik)
        if ($this->isSessionExpired($session)) {
            DB::transaction(function () use ($session) {
                /** @var CheckoutSession $locked */
                $locked = CheckoutSession::query()
                    ->where('id', $session->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($locked->status !== CheckoutSession::STATUS_EXPIRED) {
                    $locked->forceFill(['status' => CheckoutSession::STATUS_EXPIRED])->save();
                }

                // Raporlama: pending attempt varsa expired’a kapat; yoksa synthetic expired attempt oluştur.
                $this->finalizeAttemptAsExpired($locked, idempotencyKey: 'expired_read:' . $locked->id);
            });

            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'payment_session_expired');
        }

        $payable = max(
            (float) $session->cart_total - (float) $session->discount_amount,
            0
        );

        // Son attempt hatası (aynı sekme/diğer sekme refresh’te tutarlı görünmesi için)
        $latestAttempt = PaymentAttempt::query()
            ->where('checkout_session_id', $session->id)
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();

        $attemptError = null;

        if ($latestAttempt && $latestAttempt->status === PaymentAttempt::STATUS_FAILED) {
            $attemptError = $latestAttempt->error_message ?: 'Ödeme işlemi başarısız oldu.';
        } elseif ($latestAttempt && $latestAttempt->status === PaymentAttempt::STATUS_EXPIRED) {
            $attemptError = $latestAttempt->error_message ?: 'Ödeme oturumu zaman aşımına uğradı.';
        }

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
    public function show3ds(string $code, Request $request)
    {
        $session = CheckoutSession::query()
            ->where('code', $code)
            ->firstOrFail();

        if ($session->status === CheckoutSession::STATUS_COMPLETED) {
            return redirect()->to(localized_route('success'));
        }

        if ($this->isSessionExpired($session) || $session->status === CheckoutSession::STATUS_EXPIRED) {
            DB::transaction(function () use ($session) {
                /** @var CheckoutSession $locked */
                $locked = CheckoutSession::query()
                    ->where('id', $session->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($locked->status !== CheckoutSession::STATUS_EXPIRED) {
                    $locked->forceFill(['status' => CheckoutSession::STATUS_EXPIRED])->save();
                }

                $this->finalizeAttemptAsExpired($locked, idempotencyKey: 'expired_read:' . $locked->id);
            });

            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'payment_session_expired');
        }

        $token = (string) $request->query('token', '');
        $attemptId = (int) $request->query('attempt', 0);

        if ($token === '' || $attemptId <= 0) {
            return redirect()
                ->to(localized_route('payment', ['code' => $session->code]))
                ->with('err', 'payment_3ds_missing_params');
        }

        $attempt = PaymentAttempt::query()
            ->where('id', $attemptId)
            ->where('checkout_session_id', $session->id)
            ->whereNull('deleted_at')
            ->first();

        if (! $attempt || $attempt->status !== PaymentAttempt::STATUS_PENDING_3DS) {
            return redirect()
                ->to(localized_route('payment', ['code' => $session->code]))
                ->with('err', 'payment_3ds_invalid_attempt');
        }

        $expected = hash_hmac('sha256', (string) $attempt->id, (string) $attempt->idempotency_key);
        if (! hash_equals($expected, $token)) {
            return redirect()
                ->to(localized_route('payment', ['code' => $session->code]))
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
    public function complete3ds(string $code, Request $request)
    {
        $validated = $request->validate([
            'attempt_id' => ['required', 'integer', 'min:1'],
            'token'      => ['required', 'string', 'min:10'],
            'result'     => ['required', 'in:success,fail'],
        ]);

        $checkout = CheckoutSession::query()
            ->where('code', $code)
            ->firstOrFail();

        if ($checkout->status === CheckoutSession::STATUS_COMPLETED) {
            return redirect()->to(localized_route('success'));
        }

        if ($this->isSessionExpired($checkout) || $checkout->status === CheckoutSession::STATUS_EXPIRED) {
            DB::transaction(function () use ($checkout) {
                /** @var CheckoutSession $locked */
                $locked = CheckoutSession::query()
                    ->where('id', $checkout->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($locked->status !== CheckoutSession::STATUS_EXPIRED) {
                    $locked->forceFill(['status' => CheckoutSession::STATUS_EXPIRED])->save();
                }

                $this->finalizeAttemptAsExpired($locked, idempotencyKey: 'expired_read:' . $locked->id);
            });

            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'payment_session_expired');
        }

        $attempt = PaymentAttempt::query()
            ->where('id', (int) $validated['attempt_id'])
            ->where('checkout_session_id', $checkout->id)
            ->whereNull('deleted_at')
            ->firstOrFail();

        if ($attempt->status !== PaymentAttempt::STATUS_PENDING_3DS) {
            return redirect()
                ->to(localized_route('payment', ['code' => $checkout->code]))
                ->with('err', 'payment_3ds_invalid_attempt');
        }

        $expected = hash_hmac('sha256', (string) $attempt->id, (string) $attempt->idempotency_key);
        if (! hash_equals($expected, (string) $validated['token'])) {
            return redirect()
                ->to(localized_route('payment', ['code' => $checkout->code]))
                ->with('err', 'payment_3ds_invalid_token');
        }

        $gateway = PaymentGatewayFactory::make();

        if (! $gateway instanceof PaymentGateway3dsInterface) {
            return redirect()
                ->to(localized_route('payment', ['code' => $checkout->code]))
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
                ->to(localized_route('payment', ['code' => $checkout->code]));
        }

        $order = null;
        $orderCreatedNow = false;

        DB::transaction(function () use ($checkout, $attempt, $result, &$order, &$orderCreatedNow) {
            $lockedSession = CheckoutSession::query()
                ->where('id', $checkout->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedSession->status === CheckoutSession::STATUS_COMPLETED && $lockedSession->order_id) {
                $order = Order::withTrashed()->find($lockedSession->order_id);
                $orderCreatedNow = false;
                return;
            }

            $payload = (array) ($lockedSession->customer_snapshot ?? []);
            $items   = (array) ($payload['items'] ?? []);
            $meta    = (array) ($payload['metadata'] ?? []);
            $discountSnapshot = (array) ($payload['discount_snapshot'] ?? []);

            // locale: orders.locale NOT NULL → garanti
            $locale = LocaleHelper::normalizeCode(app()->getLocale());

            // customer fields (order üzerinde donmuş olsun)
            $customerName  = null;
            $customerEmail = null;
            $customerPhone = null;

            $guest = $meta['guest'] ?? null;

            if (is_array($guest)) {
                $first = trim((string) ($guest['first_name'] ?? ''));
                $last  = trim((string) ($guest['last_name'] ?? ''));
                $customerName  = trim($first . ' ' . $last) ?: null;
                $customerEmail = ! empty($guest['email']) ? (string) $guest['email'] : null;
                $customerPhone = ! empty($guest['phone']) ? (string) $guest['phone'] : null;
            } else {
                $user = $lockedSession->user_id
                    ? \App\Models\User::query()->find($lockedSession->user_id)
                    : null;

                if ($user) {
                    $customerName  = $user->name ?? null;
                    $customerEmail = $user->email ?? null;
                    $customerPhone = $user->phone ?? null;
                }
            }

            $order = Order::create([
                'user_id'            => $lockedSession->user_id,
                'status'             => 'pending',
                'payment_status'     => 'paid',
                'paid_at'            => now(),
                'payment_expires_at' => null,
                'currency'           => $lockedSession->currency,
                'total_amount'       => (float) $lockedSession->cart_total,
                'discount_amount'    => (float) $lockedSession->discount_amount,
                'billing_address'    => null,
                'coupon_snapshot'    => $discountSnapshot ?: null,
                'metadata'           => $meta ?: null,

                'locale'             => $locale,
                'customer_name'      => $customerName,
                'customer_email'     => $customerEmail,
                'customer_phone'     => $customerPhone,
            ]);

            foreach ($items as $item) {
                $snapshot = (array) ($item['snapshot'] ?? []);

                $title = $snapshot['tour_name']
                    ?? $snapshot['room_name']
                    ?? $snapshot['villa_name']
                    ?? $snapshot['hotel_name']
                    ?? 'Ürün';

                $amount     = (float) ($item['amount'] ?? 0);
                $unitPrice  = (float) ($item['unit_price'] ?? $amount);
                $totalPrice = (float) ($item['total_price'] ?? $amount);

                $order->items()->create([
                    'product_type' => $item['product_type'] ?? null,
                    'product_id'   => $item['product_id'] ?? null,
                    'title'        => $title,
                    'quantity'     => $item['quantity'] ?? 1,
                    'currency'     => $item['currency'] ?? $order->currency,
                    'unit_price'   => $unitPrice,
                    'total_price'  => $totalPrice,
                    'snapshot'     => $snapshot,
                ]);
            }

            if ($lockedSession->type === CheckoutSession::TYPE_USER && $lockedSession->user_id && $discountSnapshot) {
                $this->incrementUserCouponUsage((int) $lockedSession->user_id, $discountSnapshot);
            }

            $attempt->forceFill([
                'order_id'          => $order->id,
                'status'            => PaymentAttempt::STATUS_SUCCESS,
                'gateway_reference' => $result['gateway_reference'] ?? $attempt->gateway_reference,
                'raw_request'       => $result['raw_request'] ?? null,
                'raw_response'      => $result['raw_response'] ?? $result,
                'completed_at'      => now(),
            ])->save();

            $lockedSession->forceFill([
                'status'       => CheckoutSession::STATUS_COMPLETED,
                'completed_at' => now(),
                'order_id'     => $order->id,
            ])->save();

            $orderCreatedNow = true;
        });

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
        CouponViewModelService $couponVm,
        CampaignViewModelService $campaignVm
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
            return redirect()->to(localized_route('cart'))->with('err', 'err_cart_empty');
        }

        $items = $cart['items'];

        [$cartTotal, $cartCurrency] = $this->computeCartTotalAndCurrency($items);

        if ($cartTotal <= 0 || $cartCurrency === null) {
            return redirect()->to(localized_route('cart'))->with('err', 'err_cart_empty');
        }

        $orderNote = trim((string) $request->input('order_note'));

        $invoice = [
            'is_corporate' => (bool) $request->input('is_corporate'),
            'company'      => mb_substr(trim((string) $request->input('corp_company')), 0, 150),
            'tax_office'   => mb_substr(trim((string) $request->input('corp_tax_office')), 0, 100),
            'tax_no'       => mb_substr(trim((string) $request->input('corp_tax_no')), 0, 50),
            'address'      => mb_substr(trim((string) $request->input('corp_address')), 0, 500),
        ];

        $couponDiscountTotal   = 0.0;
        $couponSnapshot        = [];
        $campaignDiscountTotal = 0.0;
        $campaignSnapshot      = [];

        $userCurrency = CurrencyHelper::currentCode();

        $cartCoupons = $couponVm->buildCartCouponsForUser(
            $user,
            $userCurrency,
            $cartTotal,
            $cartCurrency
        );

        $appliedIds = (array) session('cart.applied_coupons', []);

        foreach ($cartCoupons as $vm) {
            $id = $vm['id'] ?? null;

            $isApplied    = $id !== null && in_array($id, $appliedIds, true);
            $isApplicable = ! empty($vm['is_applicable']);
            $discount     = (float) ($vm['calculated_discount'] ?? 0);

            if ($isApplied && $isApplicable && $discount > 0) {
                $couponDiscountTotal += $discount;

                $couponSnapshot[] = [
                    'user_coupon_id' => $id,
                    'coupon_id'      => $vm['coupon_id'] ?? null,
                    'code'           => $vm['code'] ?? null,
                    'discount'       => $discount,
                    'title'          => $vm['title'] ?? null,
                    'badge_label'    => $vm['badge_label'] ?? null,
                    'type'           => 'coupon',
                ];
            }
        }

        $cartCampaigns = $campaignVm->buildCartCampaignsForUser(
            $user,
            $items,
            $cartCurrency,
            $cartTotal
        );

        foreach ($cartCampaigns as $cvm) {
            $discount = (float) ($cvm['calculated_discount'] ?? 0);

            if (! empty($cvm['is_applicable']) && $discount > 0) {
                $campaignDiscountTotal += $discount;

                $campaignSnapshot[] = [
                    'campaign_id' => $cvm['id'],
                    'discount'    => $discount,
                    'title'       => $cvm['title'] ?? null,
                    'type'        => 'campaign',
                ];
            }
        }

        $rawDiscount    = max(0.0, $couponDiscountTotal + $campaignDiscountTotal);
        $discountAmount = $rawDiscount > 0 && $cartTotal > 0
            ? min($rawDiscount, $cartTotal)
            : 0.0;

        $payload = [
            'type'              => CheckoutSession::TYPE_USER,
            'user_id'           => $user->id,
            'items'             => $items,
            'discount_snapshot' => array_merge($couponSnapshot, $campaignSnapshot),
            'metadata'          => [
                'client_ip'     => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'customer_note' => $orderNote !== '' ? $orderNote : null,
                'invoice'       => $invoice,
            ],
        ];

        $checkout = CheckoutSession::create([
            'code'              => $this->generateCheckoutCode(),
            'type'              => CheckoutSession::TYPE_USER,
            'user_id'           => $user->id,
            'customer_snapshot' => $payload,
            'cart_total'        => $cartTotal,
            'discount_amount'   => $discountAmount,
            'currency'          => $cartCurrency,
            'status'            => CheckoutSession::STATUS_ACTIVE,
            'ip_address'        => $request->ip(),
            'user_agent'        => substr((string) $request->userAgent(), 0, 255),
            'started_at'        => now(),
            'expires_at'        => now()->addMinutes((int) config('icr.payments.order_ttl', 20)),
        ]);

        return redirect()->to(localized_route('payment', ['code' => $checkout->code]));
    }

    public function startGuest(Request $request, CampaignViewModelService $campaignVm)
    {
        $guest = $request->validate([
            'guest_first_name' => ['required', 'string', 'min:2'],
            'guest_last_name'  => ['required', 'string', 'min:2'],
            'guest_email'      => ['required', 'email'],
            'guest_phone'      => ['required', 'string', 'min:5'],
        ]);

        $cart = session('cart');
        if (! $cart || empty($cart['items'])) {
            return redirect()->to(localized_route('cart'))->with('err', 'err_cart_empty');
        }

        $items = $cart['items'];

        [$cartTotal, $cartCurrency] = $this->computeCartTotalAndCurrency($items);

        if ($cartTotal <= 0 || $cartCurrency === null) {
            return redirect()->to(localized_route('cart'))->with('err', 'err_cart_empty');
        }

        $campaignDiscountTotal = 0.0;
        $campaignSnapshot      = [];

        $cartCampaigns = $campaignVm->buildCartCampaignsForUser(
            null,
            $items,
            $cartCurrency,
            $cartTotal
        );

        foreach ($cartCampaigns as $cvm) {
            $discount = (float) ($cvm['calculated_discount'] ?? 0);

            if (! empty($cvm['is_applicable']) && $discount > 0) {
                $campaignDiscountTotal += $discount;

                $campaignSnapshot[] = [
                    'campaign_id' => $cvm['id'],
                    'discount'    => $discount,
                    'title'       => $cvm['title'] ?? null,
                    'type'        => 'campaign',
                ];
            }
        }

        $rawDiscount    = max(0.0, $campaignDiscountTotal);
        $discountAmount = $rawDiscount > 0 && $cartTotal > 0
            ? min($rawDiscount, $cartTotal)
            : 0.0;

        $payload = [
            'type'              => CheckoutSession::TYPE_GUEST,
            'user_id'           => null,
            'items'             => $items,
            'discount_snapshot' => $campaignSnapshot,
            'metadata'          => [
                'client_ip'  => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'guest'      => [
                    'first_name' => $guest['guest_first_name'],
                    'last_name'  => $guest['guest_last_name'],
                    'email'      => $guest['guest_email'],
                    'phone'      => $guest['guest_phone'],
                ],
            ],
        ];

        $checkout = CheckoutSession::create([
            'code'              => $this->generateCheckoutCode(),
            'type'              => CheckoutSession::TYPE_GUEST,
            'user_id'           => null,
            'customer_snapshot' => $payload,
            'cart_total'        => $cartTotal,
            'discount_amount'   => $discountAmount,
            'currency'          => $cartCurrency,
            'status'            => CheckoutSession::STATUS_ACTIVE,
            'ip_address'        => $request->ip(),
            'user_agent'        => substr((string) $request->userAgent(), 0, 255),
            'started_at'        => now(),
            'expires_at'        => now()->addMinutes((int) config('icr.payments.order_ttl', 20)),
        ]);

        return redirect()->to(localized_route('payment', ['code' => $checkout->code]));
    }

    public function process(string $code, Request $request)
    {
        $validated = $request->validate([
            'submit_nonce' => ['required', 'string', 'min:16'],
            'cardholder'   => ['required', 'string', 'min:3'],
            'cardnumber'   => ['required', 'string'],
            'exp-month'    => ['required', 'digits:2'],
            'exp-year'     => ['required', 'digits:2'],
            'cvc'          => ['required', 'digits_between:3,4'],
            'terms'        => ['accepted'],
        ]);

        $checkout = CheckoutSession::query()
            ->where('code', $code)
            ->firstOrFail();

        if ($checkout->status === CheckoutSession::STATUS_COMPLETED) {
            return redirect()->to(localized_route('success'));
        }

        if ($this->isSessionExpired($checkout) || $checkout->status === CheckoutSession::STATUS_EXPIRED) {
            DB::transaction(function () use ($checkout, $validated) {
                /** @var CheckoutSession $locked */
                $locked = CheckoutSession::query()
                    ->where('id', $checkout->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($locked->status !== CheckoutSession::STATUS_EXPIRED) {
                    $locked->forceFill(['status' => CheckoutSession::STATUS_EXPIRED])->save();
                }

                $this->finalizeAttemptAsExpired($locked, idempotencyKey: (string) $validated['submit_nonce']);
            });

            return redirect()
                ->to(localized_route('cart'))
                ->with('err', 'payment_session_expired');
        }

        $gateway = PaymentGatewayFactory::make();

        if (! $gateway instanceof PaymentGateway3dsInterface) {
            return redirect()
                ->to(localized_route('payment', ['code' => $checkout->code]))
                ->with('err', 'payment_3ds_not_supported');
        }

        $cardData = [
            'cardholder'   => $validated['cardholder'],
            'cardnumber'   => $validated['cardnumber'],
            'exp_month'    => $validated['exp-month'],
            'exp_year'     => $validated['exp-year'],
            'cvc'          => $validated['cvc'],
        ];

        $attempt = $this->getOrCreatePendingAttempt($checkout, (string) $validated['submit_nonce'], $request);

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
                ->to(localized_route('payment', ['code' => $checkout->code]));
        }

        $attempt->forceFill([
            'status'            => PaymentAttempt::STATUS_PENDING_3DS,
            'gateway_reference' => $start['gateway_reference'] ?? $attempt->gateway_reference,
            'raw_request'       => $start['raw_request'] ?? null,
            'raw_response'      => $start['raw_response'] ?? null,
        ])->save();

        $token = hash_hmac('sha256', (string) $attempt->id, (string) $attempt->idempotency_key);

        return redirect()->to(
            localized_route('payment.3ds', ['code' => $checkout->code]) . '?attempt=' . $attempt->id . '&token=' . $token
        );
    }

    protected function incrementUserCouponUsage(int $userId, array $discountSnapshot): void
    {
        foreach ($discountSnapshot as $row) {
            if (($row['type'] ?? null) !== 'coupon') {
                continue;
            }

            $userCouponId = $row['user_coupon_id'] ?? null;
            if (! $userCouponId) {
                continue;
            }

            $userCoupon = UserCoupon::query()
                ->where('id', $userCouponId)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (! $userCoupon) {
                continue;
            }

            $userCoupon->forceFill([
                'used_count'   => (int) $userCoupon->used_count + 1,
                'last_used_at' => now(),
            ])->save();
        }
    }

    private function computeCartTotalAndCurrency(array $items): array
    {
        $cartTotal    = 0.0;
        $cartCurrency = null;

        foreach ($items as $ci) {
            $amount = (float) ($ci['amount'] ?? 0);
            $cartTotal += $amount;

            if ($cartCurrency === null && ! empty($ci['currency'])) {
                $cartCurrency = $ci['currency'];
            }
        }

        return [$cartTotal, $cartCurrency ? strtoupper($cartCurrency) : null];
    }

    private function generateCheckoutCode(): string
    {
        return 'cs_' . bin2hex(random_bytes(8));
    }

    private function isSessionExpired(CheckoutSession $session): bool
    {
        if ($session->expires_at === null) {
            return false;
        }

        return now()->greaterThan($session->expires_at);
    }

    private function finalizeAttemptAsExpired(CheckoutSession $session, string $idempotencyKey): void
    {
        $pending = PaymentAttempt::query()
            ->where('checkout_session_id', $session->id)
            ->whereIn('status', [
                PaymentAttempt::STATUS_PENDING,
                PaymentAttempt::STATUS_PENDING_3DS,
            ])
            ->whereNull('completed_at')
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();

        if ($pending) {
            $pending->forceFill([
                'status'        => PaymentAttempt::STATUS_EXPIRED,
                'error_code'    => 'SESSION_EXPIRED',
                'error_message' => 'Ödeme oturumu zaman aşımına uğradı.',
                'completed_at'  => now(),
                'raw_response'  => ['reason' => 'checkout_session_expired'],
            ])->save();

            return;
        }

        $payable = max((float) $session->cart_total - (float) $session->discount_amount, 0);

        PaymentAttempt::create([
            'order_id'            => null,
            'checkout_session_id' => $session->id,
            'idempotency_key'     => $idempotencyKey,
            'gateway'             => config('icr.payments.driver', 'demo'),
            'amount'              => $payable,
            'currency'            => $session->currency,
            'status'              => PaymentAttempt::STATUS_EXPIRED,
            'ip_address'          => $session->ip_address,
            'user_agent'          => $session->user_agent ? substr((string) $session->user_agent, 0, 255) : null,
            'started_at'          => $session->started_at ?? now(),
            'completed_at'        => now(),
            'error_code'          => 'SESSION_EXPIRED',
            'error_message'       => 'Ödeme oturumu zaman aşımına uğradı.',
            'raw_response'        => ['reason' => 'checkout_session_expired'],
        ]);
    }

    private function getOrCreatePendingAttempt(CheckoutSession $checkout, string $idempotencyKey, Request $request): PaymentAttempt
    {
        $existing = PaymentAttempt::query()
            ->where('checkout_session_id', $checkout->id)
            ->whereIn('status', [
                PaymentAttempt::STATUS_PENDING,
                PaymentAttempt::STATUS_PENDING_3DS,
            ])
            ->whereNull('completed_at')
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            return $existing;
        }

        $payable = max((float) $checkout->cart_total - (float) $checkout->discount_amount, 0);

        try {
            return PaymentAttempt::create([
                'order_id'            => null,
                'checkout_session_id' => $checkout->id,
                'idempotency_key'     => $idempotencyKey,
                'gateway'             => config('icr.payments.driver', 'demo'),
                'amount'              => $payable,
                'currency'            => $checkout->currency,
                'status'              => PaymentAttempt::STATUS_PENDING,
                'ip_address'          => $request->ip(),
                'user_agent'          => substr((string) $request->userAgent(), 0, 255),
                'started_at'          => now(),
            ]);
        } catch (\Throwable $e) {
            $pending = PaymentAttempt::query()
                ->where('checkout_session_id', $checkout->id)
                ->whereIn('status', [
                    PaymentAttempt::STATUS_PENDING,
                    PaymentAttempt::STATUS_PENDING_3DS,
                ])
                ->whereNull('completed_at')
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->first();

            if ($pending) {
                return $pending;
            }

            throw $e;
        }
    }
}
