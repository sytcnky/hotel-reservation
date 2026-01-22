@php
    if (! function_exists('coupon_build_texts')) {
        /**
         * Kuponun ham view-model'inden, gösterilecek metinleri üretir.
         */
        function coupon_build_texts(array $coupon): array
        {
            // Durum label
            $statusLabel = match ($coupon['status'] ?? 'active') {
                'used'        => t('coupon.status.used'),
                'not_started' => t('coupon.status.not_started'),
                'expired'     => t('coupon.status.expired'),
                default       => t('coupon.status.active'),
            };

            // Geçerlilik metni
            /** @var \Illuminate\Support\Carbon|null $validFrom */
            $validFrom = $coupon['valid_from'] ?? null;

            /** @var \Illuminate\Support\Carbon|null $effectiveValidTo */
            $effectiveValidTo = $coupon['effective_valid_until'] ?? null;

            $fromText = $validFrom ? \App\Support\Date\DatePresenter::human($validFrom, 'd.m.Y') : null;
            $toText   = $effectiveValidTo ? \App\Support\Date\DatePresenter::human($effectiveValidTo, 'd.m.Y') : null;

            if ($fromText && $toText) {
                $validityText = t('coupon.validity.range', [
                    'from' => $fromText,
                    'to'   => $toText,
                ]);
            } elseif ($toText) {
                $validityText = t('coupon.validity.until', [
                    'to' => $toText,
                ]);
            } elseif ($fromText) {
                $validityText = t('coupon.validity.from', [
                    'from' => $fromText,
                ]);
            } else {
                $validityText = t('coupon.validity.unspecified');
            }

            // Alt limit metni
            $minBookingAmount   = $coupon['min_booking_amount'] ?? null;
            $minBookingCurrency = $coupon['min_booking_currency'] ?? null;
            $minNights          = $coupon['min_nights'] ?? null;

            if ($minBookingAmount && $minBookingAmount > 0 && $minBookingCurrency) {
                $altLimitText = t('coupon.limit.amount', [
                    'amount' => \App\Support\Currency\CurrencyPresenter::format(
                        $minBookingAmount,
                        $minBookingCurrency
                    ),
                ]);
            } elseif ($minNights && $minNights > 0) {
                $altLimitText = t('coupon.limit.nights', [
                    'count' => $minNights,
                ]);
            } else {
                $altLimitText = t('coupon.limit.none');
            }

            // İndirim tutarı ve maksimum indirim
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
                $maxDiscountText = t('coupon.discount.max', [
                    'amount' => \App\Support\Currency\CurrencyPresenter::format(
                        $maxDiscountAmount,
                        $maxDiscountCurrency
                    ),
                ]);
            }

            // Kalan kullanım metni
            $maxUses = $coupon['max_uses_per_user'] ?? null;
            $used    = $coupon['used_count'] ?? 0;

            if ($maxUses !== null) {
                $remaining = max(0, (int) $maxUses - (int) $used);
                $remainingText = t('coupon.usage.remaining', [
                    'count' => $remaining,
                ]);
            } else {
                $remainingText = t('coupon.usage.unlimited');
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

            $parts[] = '<div><small>' . t('coupon.label.status') . ': ' . e($texts['status_label']) . '</small></div>';
            $parts[] = '<div><small>' . e($texts['validity_text']) . '</small></div>';
            $parts[] = '<div><small>' . e($texts['alt_limit']) . '</small></div>';

            if (! empty($texts['discount_summary'])) {
                $parts[] = '<div><small>' . t('coupon.label.discount_amount') . ': ' . e($texts['discount_summary']) . '</small></div>';
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
