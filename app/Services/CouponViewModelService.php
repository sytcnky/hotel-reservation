<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use App\Models\UserCoupon;
use App\Support\Currency\CurrencyPresenter;

class CouponViewModelService
{
    /**
     * @return array{active: array<int,array>, past: array<int,array>}
     */
    public function buildBucketsForUser(User $user, string $userCurrency, string $context = 'account'): array
    {
        $userCoupons = $user->userCoupons()
            ->with('coupon')
            ->orderByDesc('assigned_at')
            ->get();

        $active = [];
        $past   = [];

        foreach ($userCoupons as $userCoupon) {
            if (! $userCoupon->coupon instanceof Coupon) {
                continue;
            }

            $vm = $this->buildViewModel($user, $userCoupon, $userCurrency);

            $maxUses = $vm['max_uses_per_user'] ?? null;
            $used    = $vm['used_count'] ?? 0;
            $remainingUses = $maxUses !== null
                ? max(0, (int) $maxUses - (int) $used)
                : null;

            $status = $vm['status'] ?? 'active';

            $goesToActive = match ($status) {
                'expired' => false,
                'used'    => $remainingUses !== null && $remainingUses > 0,
                default   => true,
            };

            if ($goesToActive) {
                $active[] = $vm;
            } else {
                $past[] = $vm;
            }
        }

        return [
            'active' => $active,
            'past'   => $past,
        ];
    }

    /**
     * Sepet sayfası için kupon VM üretir ve sepet bağlamındaki uygunluğu hesaplar.
     *
     * Kurallar:
     * - Koşullar (scope_type / product_* / min_nights / min_booking_amount) eligibility belirler.
     * - İndirim hesabı TARGET subtotal üzerinden yapılır.
     * - min_nights item-bazlıdır (tek item nights >= min yeterlidir; toplamlanmaz).
     *
     * @return array<int,array>
     */
    public function buildCartCouponsForUser(
        User $user,
        string $userCurrency,
        float $cartSubtotal,
        ?string $cartCurrency,
        array $cartItems
    ): array {
        if ($cartSubtotal <= 0 || ! $cartCurrency) {
            return [];
        }

        $cartCurrency = strtoupper((string) $cartCurrency);

        $userCoupons = $user->userCoupons()
            ->with('coupon')
            ->orderByDesc('assigned_at')
            ->get();

        $result = [];

        foreach ($userCoupons as $userCoupon) {
            if (! $userCoupon->coupon instanceof Coupon) {
                continue;
            }

            $vm = $this->buildViewModel($user, $userCoupon, $userCurrency);

            // Sepet bağlamında badge_main tutarlılığı (amount-type)
            if (($vm['discount_type'] ?? null) === 'amount') {
                $vm['badge_main'] = '';

                if (
                    ! empty($vm['discount_amount'])
                    && (float) $vm['discount_amount'] > 0
                    && ! empty($vm['discount_currency'])
                ) {
                    $vm['badge_main'] = \App\Support\Currency\CurrencyPresenter::format(
                        (float) $vm['discount_amount'],
                        (string) $vm['discount_currency']
                    );
                }
            }

            /*
             |--------------------------------------------------------------------------
             | Sepet bağlamında currency-based alanlar için TEK OTORİTE: cartCurrency
             |--------------------------------------------------------------------------
             */
            $currencyData = (array) ($userCoupon->coupon->currency_data ?? []);
            $currencyRow  = null;

            if (isset($currencyData[$cartCurrency]) && is_array($currencyData[$cartCurrency])) {
                $currencyRow = $currencyData[$cartCurrency];
            }

            // Fallback yok: sepet currency satırı yoksa ilgili alanları null bırak
            $vm['min_booking_amount']   = null;
            $vm['min_booking_currency'] = null;

            $vm['discount_amount']      = null;
            $vm['discount_currency']    = null;

            $vm['max_discount_amount']   = null;
            $vm['max_discount_currency'] = null;

            if ($currencyRow) {
                if (
                    array_key_exists('min_booking_amount', $currencyRow)
                    && $currencyRow['min_booking_amount'] !== null
                ) {
                    $vm['min_booking_amount']   = (float) $currencyRow['min_booking_amount'];
                    $vm['min_booking_currency'] = $cartCurrency;
                }

                if (array_key_exists('amount', $currencyRow) && $currencyRow['amount'] !== null) {
                    $vm['discount_amount']   = (float) $currencyRow['amount'];
                    $vm['discount_currency'] = $cartCurrency;
                }

                if (
                    array_key_exists('max_discount_amount', $currencyRow)
                    && $currencyRow['max_discount_amount'] !== null
                ) {
                    $vm['max_discount_amount']   = (float) $currencyRow['max_discount_amount'];
                    $vm['max_discount_currency'] = $cartCurrency;
                }
            }

            $status = $vm['status'] ?? 'active';
            $maxUses = $vm['max_uses_per_user'] ?? null;
            $used    = $vm['used_count'] ?? 0;
            $remainingUses = $maxUses !== null
                ? max(0, (int) $maxUses - (int) $used)
                : null;

            // Sepet kuralı: not_started / expired / hakkı bitmiş kuponlar hiç listelenmesin
            if ($status === 'not_started' || $status === 'expired') {
                continue;
            }

            if ($status === 'used' && $remainingUses !== null && $remainingUses <= 0) {
                continue;
            }

            [$isApplicable, $disabledReason, $calculatedDiscount] = $this->evaluateForCart(
                $vm,
                (float) $cartSubtotal,
                $cartCurrency,
                $cartItems
            );

            $vm['is_applicable']       = $isApplicable;
            $vm['disabled_reason']     = $disabledReason;
            $vm['calculated_discount'] = $calculatedDiscount;

            $result[] = $vm;
        }

        return $result;
    }

    public function buildViewModel(User $user, UserCoupon $pivot, string $userCurrency): array
    {
        /** @var Coupon $coupon */
        $coupon = $pivot->coupon;

        $uiLocale = app()->getLocale();

        $title = is_array($coupon->title ?? null)
            ? (string) ($coupon->title[$uiLocale] ?? '')
            : '';

        $description = is_array($coupon->description ?? null)
            ? (string) ($coupon->description[$uiLocale] ?? '')
            : '';

        $badgeLabel = is_array($coupon->badge_label ?? null)
            ? (string) ($coupon->badge_label[$uiLocale] ?? '')
            : '';

        $currencyData = (array) ($coupon->currency_data ?? []);
        $currencyRow  = null;

        if ($userCurrency && isset($currencyData[$userCurrency]) && is_array($currencyData[$userCurrency])) {
            $currencyRow = $currencyData[$userCurrency];
        }

        $minBookingAmount   = null;
        $minBookingCurrency = null;

        if (
            $currencyRow
            && array_key_exists('min_booking_amount', $currencyRow)
            && $currencyRow['min_booking_amount'] !== null
        ) {
            $minBookingAmount   = (float) $currencyRow['min_booking_amount'];
            $minBookingCurrency = $userCurrency;
        }

        $discountAmount      = null;
        $discountCurrency    = null;
        $maxDiscountAmount   = null;
        $maxDiscountCurrency = null;

        if ($currencyRow && array_key_exists('amount', $currencyRow)) {
            $discountAmount   = (float) $currencyRow['amount'];
            $discountCurrency = $userCurrency;
        }

        if ($currencyRow && array_key_exists('max_discount_amount', $currencyRow)) {
            $maxDiscountAmount   = (float) $currencyRow['max_discount_amount'];
            $maxDiscountCurrency = $userCurrency;
        }

        $validFrom        = $coupon->valid_from ?: null;
        $globalValidTo    = $coupon->valid_until ?: null;
        $userValidTo      = $pivot->expires_at ?: null;
        $effectiveValidTo = $userValidTo ?: $globalValidTo;

        $now = now();

        $status = 'active';

        if ($pivot->used_count > 0) {
            $status = 'used';
        } elseif ($validFrom && $now->lt($validFrom)) {
            $status = 'not_started';
        } elseif ($effectiveValidTo && $now->gt($effectiveValidTo)) {
            $status = 'expired';
        }

        $isActive = $status === 'active';

        $maxUsesPerUser = $coupon->max_uses_per_user;
        $usedCount      = (int) $pivot->used_count;

        $badgeMainValue = '';

        if ($coupon->discount_type === 'percent' && $coupon->percent_value !== null) {
            $pv = (float) $coupon->percent_value;

            // 10.00 -> "10", 12.50 -> "12.5"
            if (fmod($pv, 1.0) === 0.0) {
                $cleanPercent = (string) (int) $pv;
            } else {
                $cleanPercent = rtrim(rtrim(number_format($pv, 2, '.', ''), '0'), '.');
            }

            $badgeMainValue = $cleanPercent . '%';
        }

        if (
            $coupon->discount_type === 'amount'
            && $discountAmount
            && $discountAmount > 0
            && $discountCurrency
        ) {
            $badgeMainValue = CurrencyPresenter::format($discountAmount, $discountCurrency);
        }

        // TARGET defaults (DB null olabilir)
        $targetType = is_string($coupon->target_type ?? null) && trim((string) $coupon->target_type) !== ''
            ? (string) $coupon->target_type
            : 'order_total';

        return [
            'id'                    => $pivot->id,
            'coupon_id'             => $coupon->id,
            'code'                  => $coupon->code ?: ('#' . $coupon->id),

            'badge_main'            => $badgeMainValue,
            'badge_label'           => $badgeLabel,
            'title'                 => $title,
            'description'           => $description,

            'status'                => $status,
            'is_active'             => $isActive,
            'valid_from'            => $validFrom,
            'global_valid_until'    => $globalValidTo,
            'user_expires_at'       => $userValidTo,
            'effective_valid_until' => $effectiveValidTo,

            'min_booking_amount'    => $minBookingAmount,
            'min_booking_currency'  => $minBookingCurrency,

            'discount_amount'       => $discountAmount,
            'discount_currency'     => $discountCurrency,

            'max_discount_amount'   => $maxDiscountAmount,
            'max_discount_currency' => $maxDiscountCurrency,

            'min_nights'            => $coupon->min_nights,
            'max_uses_per_user'     => $maxUsesPerUser,
            'used_count'            => $usedCount,

            'discount_type'         => $coupon->discount_type,
            'percent_value'         => $coupon->percent_value,

            'is_exclusive'          => $coupon->is_exclusive,
            'scope_type'            => $coupon->scope_type,
            'product_types'         => $coupon->product_types,
            'product_domain'        => $coupon->product_domain,
            'product_id'            => $coupon->product_id,

            // TARGET (yeni)
            'target_type'           => $targetType,
            'target_product_type'   => $coupon->target_product_type,
            'target_product_domain' => $coupon->target_product_domain,
            'target_product_id'     => $coupon->target_product_id,

            'source'                => $pivot->source,
        ];
    }

    /**
     * @return array{0: bool, 1: ?string, 2: float} [isApplicable, disabledReason, calculatedDiscount]
     */
    protected function evaluateForCart(array $vm, float $cartSubtotal, ?string $cartCurrency, array $cartItems): array
    {
        // 1) Scope + min_nights eligibility
        [$scopeOk, $scopeReason, $matchedItems] = $this->evaluateScopeEligibility($vm, $cartItems);
        if (! $scopeOk) {
            return [false, $scopeReason, 0.0];
        }

        $minNights = $this->normalizePositiveInt($vm['min_nights'] ?? null);
        if ($minNights !== null) {
            [$nightsOk, $nightsReason] = $this->evaluateMinNightsEligibility($minNights, $matchedItems);
            if (! $nightsOk) {
                return [false, $nightsReason, 0.0];
            }
        }

        // 2) Currency / min limit (koşul: order_total üzerinden)
        $disabledReason = null;
        $discount       = 0.0;

        $discountCurrency = $vm['discount_currency'] ?? null;
        $minAmount        = $vm['min_booking_amount'] ?? null;
        $minCurrency      = $vm['min_booking_currency'] ?? null;

        if ($discountCurrency && $cartCurrency && $discountCurrency !== $cartCurrency) {
            $disabledReason = 'currency_mismatch';
        }

        if (
            $disabledReason === null &&
            $minAmount &&
            $minAmount > 0 &&
            $minCurrency &&
            $cartCurrency === $minCurrency &&
            $cartSubtotal < $minAmount
        ) {
            $disabledReason = 'min_limit_not_met';
        }

        // 3) TARGET subtotal (indirimin uygulanacağı baz)
        $targetSubtotal = $this->computeTargetSubtotal($vm, $cartSubtotal, $cartItems);

        if ($disabledReason === null && $targetSubtotal <= 0) {
            return [false, 'target_empty', 0.0];
        }

        // 4) Discount calculation (TARGET subtotal üzerinden)
        if ($disabledReason === null) {
            $discountType = $vm['discount_type'] ?? null;

            if ($discountType === 'percent' && isset($vm['percent_value'])) {
                $percent = (float) $vm['percent_value'];
                if ($percent > 0) {
                    $raw = $targetSubtotal * ($percent / 100);

                    $max = $vm['max_discount_amount'] ?? null;
                    $discount = $max ? min($raw, (float) $max) : $raw;

                    // safety
                    $discount = min($discount, $targetSubtotal);
                }
            } elseif ($discountType === 'amount' && isset($vm['discount_amount'])) {
                $amount = (float) $vm['discount_amount'];
                if ($amount > 0) {
                    $discount = min($amount, $targetSubtotal);
                }
            }
        }

        $isApplicable = $discount > 0 && $disabledReason === null;

        return [$isApplicable, $disabledReason, $discount];
    }

    /**
     * TARGET subtotal:
     * - order_total: cartSubtotal
     * - product_type / product: birden fazla eşleşirse SADECE en yüksek tutarlı 1 item baz alınır.
     */
    protected function computeTargetSubtotal(array $vm, float $cartSubtotal, array $cartItems): float
    {
        $targetType = is_string($vm['target_type'] ?? null) ? trim((string) $vm['target_type']) : '';
        if ($targetType === '' || $targetType === 'order_total') {
            return (float) $cartSubtotal;
        }

        if ($targetType === 'product_type') {
            $wanted = is_string($vm['target_product_type'] ?? null) ? strtolower(trim((string) $vm['target_product_type'])) : '';
            if ($wanted === '') {
                return 0.0;
            }

            $max = 0.0;

            foreach ($cartItems as $ci) {
                $pt = strtolower(trim((string) ($ci['product_type'] ?? '')));
                $norm = $this->normalizeCartProductType($pt);

                if ($norm === $wanted) {
                    $v = (float) ($ci['total_price'] ?? 0);
                    if ($v > $max) {
                        $max = $v;
                    }
                }
            }

            return max(0.0, (float) $max);
        }

        if ($targetType === 'product') {
            $domain = is_string($vm['target_product_domain'] ?? null) ? strtolower(trim((string) $vm['target_product_domain'])) : '';
            $pid    = isset($vm['target_product_id']) ? (int) $vm['target_product_id'] : 0;

            if ($domain === '' || $pid < 1) {
                return 0.0;
            }

            $max = 0.0;

            foreach ($cartItems as $ci) {
                $pt   = strtolower(trim((string) ($ci['product_type'] ?? '')));
                $snap = is_array($ci['snapshot'] ?? null) ? (array) $ci['snapshot'] : [];

                if ($domain === 'hotel') {
                    if ($pt === 'hotel_room' || $pt === 'hotel') {
                        $hid = isset($snap['hotel_id']) ? (int) $snap['hotel_id'] : 0;
                        if ($hid === $pid) {
                            $v = (float) ($ci['total_price'] ?? 0);
                            if ($v > $max) {
                                $max = $v;
                            }
                        }
                    }
                    continue;
                }

                if ($domain === 'villa') {
                    if ($pt === 'villa') {
                        $itemPid = isset($ci['product_id']) ? (int) $ci['product_id'] : 0;
                        if ($itemPid === $pid) {
                            $v = (float) ($ci['total_price'] ?? 0);
                            if ($v > $max) {
                                $max = $v;
                            }
                        }
                    }
                    continue;
                }

                if ($domain === 'tour') {
                    if ($pt === 'tour' || $pt === 'excursion') {
                        $itemPid = isset($ci['product_id']) ? (int) $ci['product_id'] : 0;
                        if ($itemPid === $pid) {
                            $v = (float) ($ci['total_price'] ?? 0);
                            if ($v > $max) {
                                $max = $v;
                            }
                        }
                    }
                    continue;
                }

                if ($domain === 'transfer') {
                    if ($pt === 'transfer') {
                        $itemPid = isset($ci['product_id']) ? (int) $ci['product_id'] : 0;
                        if ($itemPid === $pid) {
                            $v = (float) ($ci['total_price'] ?? 0);
                            if ($v > $max) {
                                $max = $v;
                            }
                        }
                    }
                    continue;
                }
            }

            return max(0.0, (float) $max);
        }

        return (float) $cartSubtotal;
    }

    /**
     * @return array{0: bool, 1: ?string, 2: array<int,array>} [ok, reason, matchedItemsForEligibility]
     */
    protected function evaluateScopeEligibility(array $vm, array $cartItems): array
    {
        $scopeType = is_string($vm['scope_type'] ?? null) ? (string) $vm['scope_type'] : 'order_total';

        if ($scopeType === 'order_total' || $scopeType === '') {
            // order_total: scope always ok; for nights check we still want all items as “matched”
            return [true, null, array_values($cartItems)];
        }

        if ($scopeType === 'product_type') {
            $types = $vm['product_types'] ?? null;
            $types = is_array($types) ? array_values(array_filter($types, fn ($v) => is_string($v) && trim($v) !== '')) : [];
            $types = array_values(array_unique(array_map(fn ($t) => strtolower(trim($t)), $types)));

            if (empty($types)) {
                return [false, 'scope_not_matched', []];
            }

            $matched = [];
            foreach ($cartItems as $ci) {
                $pt = strtolower(trim((string) ($ci['product_type'] ?? '')));
                $norm = $this->normalizeCartProductType($pt);

                if ($norm !== null && in_array($norm, $types, true)) {
                    $matched[] = $ci;
                }
            }

            if (empty($matched)) {
                return [false, 'scope_not_matched', []];
            }

            return [true, null, $matched];
        }

        if ($scopeType === 'product') {
            $domain = is_string($vm['product_domain'] ?? null) ? strtolower(trim((string) $vm['product_domain'])) : '';
            $pid    = isset($vm['product_id']) ? (int) $vm['product_id'] : 0;

            if ($domain === '' || $pid < 1) {
                return [false, 'scope_not_matched', []];
            }

            $matched = [];

            foreach ($cartItems as $ci) {
                $pt   = strtolower(trim((string) ($ci['product_type'] ?? '')));
                $snap = is_array($ci['snapshot'] ?? null) ? (array) $ci['snapshot'] : [];

                // hotel: coupon.product_id = hotel_id (snapshot.hotel_id)
                if ($domain === 'hotel') {
                    if ($pt === 'hotel_room' || $pt === 'hotel') {
                        $hid = isset($snap['hotel_id']) ? (int) $snap['hotel_id'] : 0;
                        if ($hid === $pid) {
                            $matched[] = $ci;
                        }
                    }
                    continue;
                }

                // villa: product_id = villa_id (cart item product_id)
                if ($domain === 'villa') {
                    if ($pt === 'villa') {
                        $itemPid = isset($ci['product_id']) ? (int) $ci['product_id'] : 0;
                        if ($itemPid === $pid) {
                            $matched[] = $ci;
                        }
                    }
                    continue;
                }

                // tour: product_id = tour_id (cart item product_id)
                if ($domain === 'tour') {
                    if ($pt === 'tour' || $pt === 'excursion') {
                        $itemPid = isset($ci['product_id']) ? (int) $ci['product_id'] : 0;
                        if ($itemPid === $pid) {
                            $matched[] = $ci;
                        }
                    }
                    continue;
                }

                // transfer: product_id = route_id (cart item product_id)
                if ($domain === 'transfer') {
                    if ($pt === 'transfer') {
                        $itemPid = isset($ci['product_id']) ? (int) $ci['product_id'] : 0;
                        if ($itemPid === $pid) {
                            $matched[] = $ci;
                        }
                    }
                    continue;
                }
            }

            if (empty($matched)) {
                return [false, 'scope_not_matched', []];
            }

            return [true, null, $matched];
        }

        // Unknown scope_type => not matched (fail-safe)
        return [false, 'scope_not_matched', []];
    }

    /**
     * @param array<int,array> $matchedItems
     * @return array{0: bool, 1: ?string} [ok, reason]
     */
    protected function evaluateMinNightsEligibility(int $minNights, array $matchedItems): array
    {
        $nightCapable = [];
        foreach ($matchedItems as $ci) {
            $pt = strtolower(trim((string) ($ci['product_type'] ?? '')));
            if ($pt !== 'hotel_room' && $pt !== 'hotel' && $pt !== 'villa') {
                continue;
            }

            $snap = is_array($ci['snapshot'] ?? null) ? (array) $ci['snapshot'] : [];
            $n = isset($snap['nights']) ? (int) $snap['nights'] : null;

            if ($n !== null && $n > 0) {
                $nightCapable[] = $n;
            }
        }

        if (empty($nightCapable)) {
            return [false, 'min_nights_not_supported'];
        }

        foreach ($nightCapable as $n) {
            if ($n >= $minNights) {
                return [true, null];
            }
        }

        return [false, 'min_nights_not_met'];
    }

    protected function normalizePositiveInt($value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $n = (int) $value;
        return $n > 0 ? $n : null;
    }

    /**
     * Cart item product_type -> coupon domain key normalize (hotel/villa/tour/transfer)
     */
    protected function normalizeCartProductType(string $productType): ?string
    {
        $pt = strtolower(trim($productType));

        return match ($pt) {
            'hotel_room', 'hotel' => 'hotel',
            'villa'               => 'villa',
            'tour', 'excursion'   => 'tour',
            'transfer'            => 'transfer',
            default               => null,
        };
    }
}
