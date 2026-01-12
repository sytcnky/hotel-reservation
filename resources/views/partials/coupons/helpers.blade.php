@php
    if (! function_exists('coupon_build_texts')) {
        /**
         * Kuponun ham view-model'inden, gösterilecek metinleri üretir.
         * Buradaki Türkçe metinler ileride t() / çeviri altyapısına taşınacak.
         */
        function coupon_build_texts(array $coupon): array
        {
            // Durum label
            $statusLabel = match ($coupon['status'] ?? 'active') {
                'used'        => 'Kullanıldı',
                'not_started' => 'Henüz başlamadı',
                'expired'     => 'Süresi doldu',
                default       => 'Aktif',
            };

            // Geçerlilik metni
            /** @var \Illuminate\Support\Carbon|null $validFrom */
            $validFrom = $coupon['valid_from'] ?? null;

            /** @var \Illuminate\Support\Carbon|null $effectiveValidTo */
            $effectiveValidTo = $coupon['effective_valid_until'] ?? null;

            if ($validFrom && $effectiveValidTo) {
                $validityText = sprintf(
                    'Geçerlilik: %s – %s',
                    $validFrom->format('d.m.Y'),
                    $effectiveValidTo->format('d.m.Y')
                );
            } elseif ($effectiveValidTo) {
                $validityText = 'Son kullanım: ' . $effectiveValidTo->format('d.m.Y');
            } elseif ($validFrom) {
                $validityText = 'Başlangıç: ' . $validFrom->format('d.m.Y');
            } else {
                $validityText = 'Geçerlilik tarihi: Belirtilmemiş';
            }

            // Alt limit metni
            $minBookingAmount   = $coupon['min_booking_amount'] ?? null;
            $minBookingCurrency = $coupon['min_booking_currency'] ?? null;
            $minNights          = $coupon['min_nights'] ?? null;

            if ($minBookingAmount && $minBookingAmount > 0 && $minBookingCurrency) {
                $altLimitText = 'Alt limit: ' .
                    \App\Support\Currency\CurrencyPresenter::format($minBookingAmount, $minBookingCurrency);
            } elseif ($minNights && $minNights > 0) {
                $altLimitText = 'Alt limit: ' . $minNights . ' Gece';
            } else {
                $altLimitText = 'Alt limit: Yok';
            }

            // İndirim tutarı ve maksimum indirim (aktif para birimi için)
            $discountAmount      = $coupon['discount_amount'] ?? null;
            $discountCurrency    = $coupon['discount_currency'] ?? null;
            $maxDiscountAmount   = $coupon['max_discount_amount'] ?? null;
            $maxDiscountCurrency = $coupon['max_discount_currency'] ?? null;

            $discountSummary = null;
            if ($discountAmount && $discountAmount > 0 && $discountCurrency) {
                $discountSummary = \App\Support\Currency\CurrencyPresenter::format($discountAmount, $discountCurrency);
            }

            $maxDiscountText = null;
            if ($maxDiscountAmount && $maxDiscountAmount > 0 && $maxDiscountCurrency) {
                $maxDiscountText = 'Maksimum indirim: ' .
                    \App\Support\Currency\CurrencyPresenter::format($maxDiscountAmount, $maxDiscountCurrency);
            }

            // Kalan kullanım metni
            $remainingText = '';
            $maxUses = $coupon['max_uses_per_user'] ?? null;
            $used    = $coupon['used_count'] ?? 0;

            if ($maxUses !== null) {
                $remaining = max(0, (int) $maxUses - (int) $used);
                $remainingText = 'Kalan kullanım: ' . $remaining;
            } else {
                $remainingText = 'Kullanım sınırı yok';
            }

            return [
                'status_label'     => $statusLabel,
                'validity_text'    => $validityText,
                'alt_limit'        => $altLimitText,
                'remaining'        => $remainingText,
                'discount_summary' => $discountSummary,
                'max_discount'     => $maxDiscountText,
            ];
        }
    }

    if (! function_exists('coupon_build_tooltip_html')) {
        /**
         * Tooltip HTML'ini üretir.
         * HTML layout burada; metinler coupon_build_texts ile üretilen değerlerden gelir.
         */
        function coupon_build_tooltip_html(array $coupon): string
        {
            $texts = coupon_build_texts($coupon);

            $parts = [];

            $title = $coupon['title'] ?: $coupon['code'];
            if ($title) {
                $parts[] = '<div><strong>' . e($title) . '</strong></div>';
            }

            if (! empty($coupon['description'])) {
                $parts[] = '<div>' . e($coupon['description']) . '</div>';
            }

            $parts[] = '<hr>';

            $parts[] = '<div><small>Durum: ' . e($texts['status_label']) . '</small></div>';
            $parts[] = '<div><small>' . e($texts['validity_text']) . '</small></div>';
            $parts[] = '<div><small>' . e($texts['alt_limit']) . '</small></div>';

            if (! empty($texts['discount_summary'])) {
                $parts[] = '<div><small>İndirim tutarı: ' . e($texts['discount_summary']) . '</small></div>';
            }

            if (! empty($texts['max_discount'])) {
                $parts[] = '<div><small>' . e($texts['max_discount']) . '</small></div>';
            }

            if (! empty($texts['remaining'])) {
                $parts[] = '<div><small>' . e($texts['remaining']) . '</small></div>';
            }

            return implode('', $parts);
        }
    }
@endphp
