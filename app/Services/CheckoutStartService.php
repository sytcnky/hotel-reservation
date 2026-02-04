<?php

namespace App\Services;

use App\Models\CheckoutSession;
use App\Models\User;
use App\Support\Helpers\CurrencyHelper;
use Illuminate\Http\Request;

class CheckoutStartService
{
    public function __construct(
        protected CartInvariant $cartInvariant,
        protected CouponViewModelService $couponVm,
        protected CampaignViewModelService $campaignVm,
    ) {
    }

    /**
     * Auth user checkout başlatır.
     *
     * Davranış:
     * - cart invariant: mismatch varsa false
     * - coupon + campaign discount snapshot üretir
     * - CheckoutSession oluşturur ve döner
     *
     * Idempotency (P0):
     * - Aynı user + aynı cart_hash için aktif (expire olmamış) checkout varsa YENİ oluşturmaz, onu döner.
     *
     * @return array{ok: bool, err?: string, checkout?: CheckoutSession}
     */
    public function startForUser(User $user, Request $request, array $items): array
    {
        // 1) Mixed currency guard (fail-fast) + total/currency
        if ($this->cartInvariant->resetIfCurrencyMismatch($items)) {
            return ['ok' => false, 'err' => 'msg.err.cart.currency_mismatch'];
        }

        [$cartTotal, $cartCurrency] = $this->cartInvariant->computeSubtotalAndCurrency($items);

        if ($cartTotal <= 0 || ! $cartCurrency) {
            return ['ok' => false, 'err' => 'msg.err.cart.empty'];
        }

        // 1.1) Idempotency key (same cart => same hash)
        $cartHash = $this->computeCartHash($items, (string) $cartCurrency);

        // 1.2) Reuse active checkout for same user + same cart hash (prevents double tab / double click)
        $existing = CheckoutSession::query()
            ->where('type', CheckoutSession::TYPE_USER)
            ->where('user_id', $user->id)
            ->where('status', CheckoutSession::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->whereRaw("customer_snapshot->'metadata'->>'cart_hash' = ?", [$cartHash])
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            return ['ok' => true, 'checkout' => $existing];
        }

        // 2) Metadata (note + invoice)
        $orderNote = trim((string) $request->input('order_note'));

        $invoice = [
            'is_corporate' => (bool) $request->input('is_corporate'),
            'company'      => mb_substr(trim((string) $request->input('corp_company')), 0, 150),
            'tax_office'   => mb_substr(trim((string) $request->input('corp_tax_office')), 0, 100),
            'tax_no'       => mb_substr(trim((string) $request->input('corp_tax_no')), 0, 50),
            'address'      => mb_substr(trim((string) $request->input('corp_address')), 0, 500),
        ];

        // 3) Discounts
        $couponDiscountTotal   = 0.0;
        $couponSnapshot        = [];
        $campaignDiscountTotal = 0.0;
        $campaignSnapshot      = [];

        $userCurrency = CurrencyHelper::currentCode();

        $cartCoupons = $this->couponVm->buildCartCouponsForUser(
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

        $cartCampaigns = $this->campaignVm->buildCartCampaignsForUser(
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
                    'subtitle'    => $cvm['subtitle'] ?? null,
                    'type'        => 'campaign',
                ];
            }
        }

        $rawDiscount    = max(0.0, $couponDiscountTotal + $campaignDiscountTotal);
        $discountAmount = $rawDiscount > 0 && $cartTotal > 0
            ? min($rawDiscount, $cartTotal)
            : 0.0;

        // 4) customer_snapshot payload (kilitli sözleşme: items + discount_snapshot + metadata)
        $payload = [
            'type'              => CheckoutSession::TYPE_USER,
            'user_id'           => $user->id,
            'items'             => $items,
            'discount_snapshot' => array_merge($couponSnapshot, $campaignSnapshot),
            'metadata'          => [
                'cart_hash'     => $cartHash,

                'client_ip'     => $request->ip(),
                'user_agent'    => substr((string) $request->userAgent(), 0, 255),
                'customer_note' => $orderNote !== '' ? $orderNote : null,
                'invoice'       => $invoice,
            ],
        ];

        $ttlMinutes = (int) config('icr.payments.order_ttl', 15);
        if ($ttlMinutes < 1) {
            $ttlMinutes = 1;
        }

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
            'expires_at'        => now()->addMinutes($ttlMinutes),
        ]);

        return ['ok' => true, 'checkout' => $checkout];
    }

    /**
     * Guest checkout başlatır.
     *
     * Idempotency (P0):
     * - Aynı guest email + aynı cart_hash için aktif checkout varsa YENİ oluşturmaz, onu döner.
     *
     * @param array{guest_first_name:string,guest_last_name:string,guest_email:string,guest_phone:string} $guest
     * @return array{ok: bool, err?: string, checkout?: CheckoutSession}
     */
    public function startForGuest(array $guest, Request $request, array $items): array
    {
        // 1) Mixed currency guard (fail-fast) + total/currency
        if ($this->cartInvariant->resetIfCurrencyMismatch($items)) {
            return ['ok' => false, 'err' => 'msg.err.cart.currency_mismatch'];
        }

        [$cartTotal, $cartCurrency] = $this->cartInvariant->computeSubtotalAndCurrency($items);

        if ($cartTotal <= 0 || ! $cartCurrency) {
            return ['ok' => false, 'err' => 'msg.err.cart.empty'];
        }

        $cartHash  = $this->computeCartHash($items, (string) $cartCurrency);
        $guestEmail = (string) ($guest['guest_email'] ?? '');

        // Reuse active guest checkout for same email + same cart hash
        $existing = CheckoutSession::query()
            ->where('type', CheckoutSession::TYPE_GUEST)
            ->whereNull('user_id')
            ->where('status', CheckoutSession::STATUS_ACTIVE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->whereRaw("customer_snapshot->'metadata'->>'cart_hash' = ?", [$cartHash])
            ->whereRaw("customer_snapshot->'metadata'->'guest'->>'email' = ?", [$guestEmail])
            ->orderByDesc('id')
            ->first();

        if ($existing) {
            return ['ok' => true, 'checkout' => $existing];
        }

        // 2) Campaign discounts (guest: coupon yok)
        $campaignDiscountTotal = 0.0;
        $campaignSnapshot      = [];

        $cartCampaigns = $this->campaignVm->buildCartCampaignsForUser(
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

        // 3) customer_snapshot payload
        $payload = [
            'type'              => CheckoutSession::TYPE_GUEST,
            'user_id'           => null,
            'items'             => $items,
            'discount_snapshot' => $campaignSnapshot,
            'metadata'          => [
                'cart_hash'  => $cartHash,

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

        $ttlMinutes = (int) config('icr.payments.order_ttl', 15);
        if ($ttlMinutes < 1) {
            $ttlMinutes = 1;
        }

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
            'expires_at'        => now()->addMinutes($ttlMinutes),
        ]);

        return ['ok' => true, 'checkout' => $checkout];
    }

    private function computeCartHash(array $items, string $cartCurrency): string
    {
        // Deterministic encoding (no assumptions about item ordering stability)
        $normalized = $this->deepSort($items);

        $payload = [
            'currency' => strtoupper(trim($cartCurrency)),
            'items'    => $normalized,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash('sha256', (string) $json);
    }

    private function deepSort($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        // Sort associative arrays by key; keep numeric arrays as-is but deep sort elements
        $isAssoc = array_keys($value) !== range(0, count($value) - 1);

        foreach ($value as $k => $v) {
            $value[$k] = $this->deepSort($v);
        }

        if ($isAssoc) {
            ksort($value);
        }

        return $value;
    }

    private function generateCheckoutCode(): string
    {
        return 'cs_' . bin2hex(random_bytes(8));
    }
}
