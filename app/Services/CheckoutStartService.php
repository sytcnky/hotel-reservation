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
     * @return array{ok: bool, err?: string, checkout?: CheckoutSession}
     */
    public function startForUser(User $user, Request $request, array $items): array
    {
        if ($this->cartInvariant->resetIfCurrencyMismatch($items)) {
            return ['ok' => false, 'err' => 'msg.err.cart.currency_mismatch'];
        }

        [$cartTotal, $cartCurrency] = $this->cartInvariant->computeSubtotalAndCurrency($items);

        if ($cartTotal <= 0 || ! $cartCurrency) {
            return ['ok' => false, 'err' => 'msg.err.cart.empty'];
        }

        $cartHash = $this->computeCartHash($items, (string) $cartCurrency);

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

        $cartCoupons = $this->couponVm->buildCartCouponsForUser(
            $user,
            $userCurrency,
            (float) $cartTotal,
            (string) $cartCurrency,
            $items
        );

        /*
         |--------------------------------------------------------------------------
         | applied ids normalize + stale cleanup (kuponlar listelenmiyorsa düşür)
         |--------------------------------------------------------------------------
         */
        $appliedIds = (array) session('cart.applied_coupons', []);
        $appliedIds = array_values(array_unique(array_map('intval', $appliedIds)));

        $couponMap = []; // id => is_applicable(bool)
        foreach ($cartCoupons as $vm) {
            $id = isset($vm['id']) ? (int) $vm['id'] : 0;
            if ($id > 0) {
                $couponMap[$id] = ! empty($vm['is_applicable']);
            }
        }

        $normalizedApplied = [];
        foreach ($appliedIds as $aid) {
            $aid = (int) $aid;
            if ($aid < 1) {
                continue;
            }

            // cartCoupons'a hiç girmeyen -> stale
            if (! array_key_exists($aid, $couponMap)) {
                continue;
            }

            // applicable değilse applied kalmasın
            if ($couponMap[$aid] !== true) {
                continue;
            }

            $normalizedApplied[] = $aid;
        }
        $normalizedApplied = array_values(array_unique($normalizedApplied));

        if ($normalizedApplied !== $appliedIds) {
            session(['cart.applied_coupons' => $normalizedApplied]);
        }
        $appliedIds = $normalizedApplied;

        foreach ($cartCoupons as $vm) {
            $id = isset($vm['id']) ? (int) $vm['id'] : 0;

            $isApplied    = $id > 0 && in_array($id, $appliedIds, true);
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
            (string) $cartCurrency,
            (float) $cartTotal
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
            'cart_total'        => (float) $cartTotal,
            'discount_amount'   => (float) $discountAmount,
            'currency'          => (string) $cartCurrency,
            'status'            => CheckoutSession::STATUS_ACTIVE,
            'ip_address'        => $request->ip(),
            'user_agent'        => substr((string) $request->userAgent(), 0, 255),
            'started_at'        => now(),
            'expires_at'        => now()->addMinutes($ttlMinutes),
        ]);

        return ['ok' => true, 'checkout' => $checkout];
    }

    /**
     * @return array{ok: bool, err?: string, checkout?: CheckoutSession}
     */
    public function startForGuest(array $guest, Request $request, array $items): array
    {
        if ($this->cartInvariant->resetIfCurrencyMismatch($items)) {
            return ['ok' => false, 'err' => 'msg.err.cart.currency_mismatch'];
        }

        [$cartTotal, $cartCurrency] = $this->cartInvariant->computeSubtotalAndCurrency($items);

        if ($cartTotal <= 0 || ! $cartCurrency) {
            return ['ok' => false, 'err' => 'msg.err.cart.empty'];
        }

        $cartHash   = $this->computeCartHash($items, (string) $cartCurrency);
        $guestEmail = (string) ($guest['guest_email'] ?? '');

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

        $campaignDiscountTotal = 0.0;
        $campaignSnapshot      = [];

        $cartCampaigns = $this->campaignVm->buildCartCampaignsForUser(
            null,
            $items,
            (string) $cartCurrency,
            (float) $cartTotal
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
            'cart_total'        => (float) $cartTotal,
            'discount_amount'   => (float) $discountAmount,
            'currency'          => (string) $cartCurrency,
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

        $isAssoc = array_keys($value) !== range(0, count($value) - 1);

        foreach ($value as $k => $v) {
            $value[$k] = $this->deepSort($v);
        }

        if ($isAssoc) {
            ksort($value);
            return $value;
        }

        // numeric list: make deterministic order
        usort($value, function ($a, $b) {
            $sa = $this->itemSortSignature($a);
            $sb = $this->itemSortSignature($b);

            return $sa <=> $sb;
        });

        return $value;
    }

    private function itemSortSignature($item): string
    {
        if (! is_array($item)) {
            return (string) $item;
        }

        $pt = strtolower(trim((string) ($item['product_type'] ?? '')));
        $pid = isset($item['product_id']) ? (int) $item['product_id'] : 0;
        $cur = strtoupper(trim((string) ($item['currency'] ?? '')));
        $tot = isset($item['total_price']) ? (string) $item['total_price'] : '';

        $snap = is_array($item['snapshot'] ?? null) ? (array) $item['snapshot'] : [];

        $d1 = (string) ($snap['checkin'] ?? $snap['date'] ?? '');
        $d2 = (string) ($snap['checkout'] ?? '');

        return $pt . '|' . $pid . '|' . $cur . '|' . $tot . '|' . $d1 . '|' . $d2;
    }

    private function generateCheckoutCode(): string
    {
        return 'cs_' . bin2hex(random_bytes(8));
    }
}
