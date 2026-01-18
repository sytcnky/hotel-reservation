<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use App\Models\UserCoupon;
use App\Support\Currency\CurrencyPresenter;

class CouponViewModelService
{
    /**
     * Kullanıcıya ait kuponları bucket’lara ayırır (Hesabım sayfası).
     *
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

            // Kalan kullanım hakkı
            $maxUses = $vm['max_uses_per_user'] ?? null;
            $used    = $vm['used_count'] ?? 0;
            $remainingUses = $maxUses !== null
                ? max(0, (int) $maxUses - (int) $used)
                : null;

            $status = $vm['status'] ?? 'active';

            // Şimdilik sadece account context'i kullanılıyor.
            $goesToActive = match ($status) {
                'expired' => false,
                'used'    => $remainingUses !== null && $remainingUses > 0,
                default   => true, // active, not_started, bilinmeyen
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
     * Sepet sayfası için, sadece listelenmesi gereken kuponları ve
     * sepet bağlamındaki uygunluk durumlarını hesaplar.
     *
     * NOT:
     * - not_started / expired / hakkı bitmiş kuponlar hiç dönmez.
     * - scope / product_types / min_nights vb. ileri kurallar şimdilik hesaplanmıyor (TODO).
     *
     * @return array<int,array>  Kupon VM + is_applicable / disabled_reason / calculated_discount
     */
    public function buildCartCouponsForUser(
        User $user,
        string $userCurrency,
        float $cartSubtotal,
        ?string $cartCurrency
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

            // Genel VM (account vb. context’lerle ortak)
            $vm = $this->buildViewModel($user, $userCoupon, $userCurrency);

            // Sepet bağlamında badge_main tutarlılığı:
            // Amount-type kuponlarda badge_main, cartCurrency ile gösterilmeli.
            // Percent-type zaten currency bağımsız.
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
             |
             | Bu override, min limit ve amount-type indirimlerde “userCurrency” yerine
             | sepetin currency’sini baz alır.
             */
            $currencyData = (array) ($userCoupon->coupon->currency_data ?? []);
            $currencyRow  = null;

            if (isset($currencyData[$cartCurrency]) && is_array($currencyData[$cartCurrency])) {
                $currencyRow = $currencyData[$cartCurrency];
            }

            // Varsayılan: sepet bağlamında currency satırı yoksa ilgili alanları null bırak (fallback üretme)
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

            // Sepet kuralın: not_started / expired / hakkı bitmiş kuponlar hiç listelenmesin.
            if ($status === 'not_started' || $status === 'expired') {
                continue;
            }

            if ($status === 'used' && $remainingUses !== null && $remainingUses <= 0) {
                continue;
            }

            [$isApplicable, $disabledReason, $calculatedDiscount] = $this->evaluateForCart(
                $vm,
                $cartSubtotal,
                $cartCurrency
            );

            $vm['is_applicable']       = $isApplicable;
            $vm['disabled_reason']     = $disabledReason;
            $vm['calculated_discount'] = $calculatedDiscount;

            $result[] = $vm;
        }

        return $result;
    }

    /**
     * Tek bir UserCoupon kaydından view-model üretir.
     */
    public function buildViewModel(User $user, UserCoupon $pivot, string $userCurrency): array
    {
        /** @var Coupon $coupon */
        $coupon = $pivot->coupon;

        $uiLocale = app()->getLocale();

        /*
         |--------------------------------------------------------------------------
         | Çoklu dil alanları (title / description / badge_main / badge_label)
         |--------------------------------------------------------------------------
         */
        $title = is_array($coupon->title ?? null)
            ? (string) ($coupon->title[$uiLocale] ?? '')
            : '';

        $description = is_array($coupon->description ?? null)
            ? (string) ($coupon->description[$uiLocale] ?? '')
            : '';

        $badgeLabel = is_array($coupon->badge_label ?? null)
            ? (string) ($coupon->badge_label[$uiLocale] ?? '')
            : '';

        /*
         |--------------------------------------------------------------------------
         | Para birimi bazlı alt limit / maksimum indirim
         |--------------------------------------------------------------------------
         |
         | NOT: currency_data model cast (array) üzerinden okunur.
         | getRawOriginal + json_decode kaldırıldı.
         */
        $currencyData = (array) ($coupon->currency_data ?? []);

        $currencyRow = null;

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

        // İndirim tutarı ve maksimum indirim (aktif para birimi için)
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

        /*
         |--------------------------------------------------------------------------
         | Geçerlilik tarihleri
         |--------------------------------------------------------------------------
         */
        $validFrom        = $coupon->valid_from ?: null;
        $globalValidTo    = $coupon->valid_until ?: null;
        $userValidTo      = $pivot->expires_at ?: null;
        $effectiveValidTo = $userValidTo ?: $globalValidTo;

        $now = now();

        /*
         |--------------------------------------------------------------------------
         | Durum hesaplama (kod)
         |--------------------------------------------------------------------------
         */
        $status = 'active';

        if ($pivot->used_count > 0) {
            $status = 'used';
        } elseif ($validFrom && $now->lt($validFrom)) {
            $status = 'not_started';
        } elseif ($effectiveValidTo && $now->gt($effectiveValidTo)) {
            $status = 'expired';
        }

        $isActive = $status === 'active';

        /*
         |--------------------------------------------------------------------------
         | Kullanım sınırı için ham veriler
         |--------------------------------------------------------------------------
         */
        $maxUsesPerUser = $coupon->max_uses_per_user;
        $usedCount      = (int) $pivot->used_count;

        /*
         |--------------------------------------------------------------------------
         | Badge main — sadece fiyat / yüzde verisinden üretilir
         |--------------------------------------------------------------------------
         */
        $badgeMainValue = '';

        // Yüzdelik indirim: %15 gibi
        if ($coupon->discount_type === 'percent' && $coupon->percent_value !== null) {
            $cleanPercent   = rtrim(rtrim((string) $coupon->percent_value, '0'), '.');
            $badgeMainValue = $cleanPercent . '%';
        }

        // Tutar tipi indirim: 300₺, 50£ gibi
        if (
            $coupon->discount_type === 'amount'
            && $discountAmount
            && $discountAmount > 0
            && $discountCurrency
        ) {
            $badgeMainValue = CurrencyPresenter::format($discountAmount, $discountCurrency);
        }

        return [
            // Pivot id (UserCoupon)
            'id'                    => $pivot->id,

            // P0-2 için sözleşme alanı (Coupon id) — PaymentController snapshot mapping bunu kullanacak.
            'coupon_id'             => $coupon->id,

            'code'                  => $coupon->code ?: ('#' . $coupon->id),

            'badge_main'            => $badgeMainValue,
            'badge_label'           => $badgeLabel,
            'title'                 => $title,
            'description'           => $description,

            // Durum / tarihler (ham veri)
            'status'                => $status,             // active | used | not_started | expired
            'is_active'             => $isActive,
            'valid_from'            => $validFrom,
            'global_valid_until'    => $globalValidTo,
            'user_expires_at'       => $userValidTo,
            'effective_valid_until' => $effectiveValidTo,

            // Alt limit / kullanım sınırı (ham veri)
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

            'source'                => $pivot->source,
        ];
    }

    /**
     * Sepet bağlamında tek bir kupon view-model’ini değerlendirir.
     *
     * Geri dönüş:
     *  - bool   $isApplicable       → Uygula butonu gösterilebilir mi?
     *  - ?string $disabledReason    → Gösterilecek sebep (kullanılamıyorsa, kırmızı text)
     *  - float  $calculatedDiscount → Uygulansa ne kadar indirim yapar?
     */
    protected function evaluateForCart(array $vm, float $cartSubtotal, ?string $cartCurrency): array
    {
        $disabledReason = null;
        $discount       = 0.0;

        $discountCurrency = $vm['discount_currency'] ?? null;
        $minAmount        = $vm['min_booking_amount'] ?? null;
        $minCurrency      = $vm['min_booking_currency'] ?? null;

        // İndirim para birimi uyumsuzluğu
        if ($discountCurrency && $cartCurrency && $discountCurrency !== $cartCurrency) {
            $disabledReason = 'currency_mismatch';
        }

        // ALT LİMİT kontrolü (discount_currency uyuşsa bile min limit ayrı değerlendirilir)
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

        // İndirim hesabı
        if ($disabledReason === null) {
            $discountType = $vm['discount_type'] ?? null;

            if ($discountType === 'percent' && isset($vm['percent_value'])) {
                $percent = (float) $vm['percent_value'];
                if ($percent > 0) {
                    $raw = $cartSubtotal * ($percent / 100);
                    $max = $vm['max_discount_amount'] ?? null;
                    $discount = $max ? min($raw, (float) $max) : $raw;
                }
            } elseif ($discountType === 'amount' && isset($vm['discount_amount'])) {
                $amount = (float) $vm['discount_amount'];
                if ($amount > 0) {
                    $discount = min($amount, $cartSubtotal);
                }
            }
        }

        $isApplicable = $discount > 0 && $disabledReason === null;

        return [$isApplicable, $disabledReason, $discount];
    }
}
