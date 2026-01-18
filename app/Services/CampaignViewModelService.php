<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\Order;
use App\Models\User;
use App\Support\Helpers\LocaleHelper;
use Carbon\Carbon;

class CampaignViewModelService
{
    /**
     * Sepet sayfası için kampanya listesi + hesaplanmış indirim.
     *
     * @param  User   $user
     * @param  array  $cartItems      session('cart.items') içeriği
     * @param  string $cartCurrency   Sepet para birimi
     * @param  float  $cartSubtotal   Sepet toplam tutarı
     * @return array<int,array>       Her kampanya için VM:
     *                                [
     *                                  'id' => ...,
     *                                  'title' => ...,
     *                                  'discount_type' => 'percent' | 'amount',
     *                                  'percent_value' => ?float,
     *                                  'amount_value' => ?float,
     *                                  'max_discount_value' => ?float,
     *                                  'conditions' => array,
     *                                  'is_applicable' => bool,
     *                                  'disabled_reason' => ?string,
     *                                  'calculated_discount' => float,
     *                                ]
     */
    public function buildCartCampaignsForUser(
        ?User $user,
        array $cartItems,
        string $cartCurrency,
        float $cartSubtotal
    ): array {
        if ($cartSubtotal <= 0) {
            return [];
        }

        $today = Carbon::today();

        $campaigns = Campaign::query()
            ->where('is_active', true)
            ->where('visible_on_web', true)
            ->where(function ($q) use ($today) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->orderByDesc('priority')
            ->get();

        $result = [];

        foreach ($campaigns as $campaign) {
            $vm = $this->buildViewModel($campaign, $cartCurrency);

            [$isApplicable, $discount, $reason] = $this->evaluateForCart(
                $campaign,
                $vm,
                $user,
                $cartItems,
                $cartCurrency,
                $cartSubtotal
            );

            $vm['is_applicable']       = $isApplicable;
            $vm['disabled_reason']     = $reason;
            $vm['calculated_discount'] = $discount;

            $result[] = $vm;
        }

        return $result;
    }

    /**
     * Tek bir Campaign kaydından view-model üretir.
     */
    protected function buildViewModel(Campaign $campaign, string $cartCurrency): array
    {
        $baseLocale = LocaleHelper::defaultCode();
        $uiLocale   = app()->getLocale();

        // content: { "tr": { "title": "...", ... }, "en": { ... } }
        $content = (array) ($campaign->content ?? []);

        $localeBlock = [];

        if (isset($content[$uiLocale]) && is_array($content[$uiLocale])) {
            $localeBlock = $content[$uiLocale];
        } elseif (isset($content[$baseLocale]) && is_array($content[$baseLocale])) {
            $localeBlock = $content[$baseLocale];
        }

        $title    = $localeBlock['title'] ?? null;
        $subtitle = $localeBlock['subtitle'] ?? null;

        $discount     = (array) ($campaign->discount ?? []);
        $discountType = $discount['type'] ?? 'percent';

        // Para birimi satırı (currency_data[cartCurrency])
        $currencyData = (array) ($discount['currency_data'] ?? []);
        $currencyRow  = null;

        if (isset($currencyData[$cartCurrency]) && is_array($currencyData[$cartCurrency])) {
            $currencyRow = $currencyData[$cartCurrency];
        }

        $percentValue = null;
        $amountValue  = null;
        $maxDiscount  = null;

        // Yüzdelik indirim -> percent_value kök seviyede
        if ($discountType === 'percent') {
            if (array_key_exists('percent_value', $discount)) {
                $percentValue = $discount['percent_value'] !== null
                    ? (float) $discount['percent_value']
                    : null;
            }
        }

        // Tutar tipi indirim -> currency_data[CUR].amount
        if ($discountType === 'amount') {
            if ($currencyRow && array_key_exists('amount', $currencyRow)) {
                $amountValue = $currencyRow['amount'] !== null
                    ? (float) $currencyRow['amount']
                    : null;
            }
        }

        // Tavan indirim -> currency_data[CUR].max_discount_amount
        if ($currencyRow && array_key_exists('max_discount_amount', $currencyRow)) {
            $maxDiscount = $currencyRow['max_discount_amount'] !== null
                ? (float) $currencyRow['max_discount_amount']
                : null;
        }

        // Koşullar JSON'u olduğu gibi taşınır
        $conditions = (array) ($campaign->conditions ?? []);

        return [
            'id'                 => $campaign->id,
            'title'              => $title ?: ($campaign->name ?? ('Campaign #' . $campaign->id)),
            'subtitle'           => $subtitle,

            'discount_type'      => $discountType,
            'percent_value'      => $percentValue,
            'amount_value'       => $amountValue,
            'max_discount_value' => $maxDiscount,

            'conditions'         => $conditions,

            'global_usage_limit' => $campaign->global_usage_limit,
            'user_usage_limit'   => $campaign->user_usage_limit,
            'usage_count'        => $campaign->usage_count,
        ];
    }

    /**
     * Sepet bağlamında tek bir kampanyayı değerlendirir.
     *
     * Geri dönüş:
     *  - bool    $isApplicable
     *  - float   $discount
     *  - ?string $disabledReason
     */
    protected function evaluateForCart(
        Campaign $campaign,
        array $vm,
        ?User $user,
        array $cartItems,
        string $cartCurrency,
        float $cartSubtotal
    ): array {
        $disabledReason = null;

        // 1) Global kullanım limiti (kampanya genelinde)
        $globalLimit = $vm['global_usage_limit'] ?? null;
        $usageCount  = $vm['usage_count'] ?? 0;

        if ($globalLimit !== null && $globalLimit >= 0 && $usageCount >= $globalLimit) {
            return [false, 0.0, 'global_usage_limit_reached'];
        }

        // 2) Koşullar
        $conditions = (array) ($vm['conditions'] ?? []);
        $rules      = (array) ($conditions['rules'] ?? []);

        // İndirim hangi satırlara uygulanacak?
        $discountBaseAmount   = $cartSubtotal;
        $targetProductTypes   = null;

        foreach ($rules as $rule) {
            $type = $rule['type'] ?? null;

            if (! $type) {
                continue;
            }

            switch ($type) {
                case 'basket_required_product_types':
                    $required = (array) ($rule['required_types'] ?? []);

                    foreach ($required as $reqType) {
                        $hasType = collect($cartItems)->contains(function (array $item) use ($reqType) {
                            return ($item['product_type'] ?? null) === $reqType;
                        });

                        if (! $hasType) {
                            return [false, 0.0, 'basket_required_product_types'];
                        }
                    }
                    break;

                case 'discount_target_product_types':
                    $targetProductTypes = array_values((array) ($rule['product_types'] ?? []));
                    break;

                case 'user_order_count':
                    // Misafir için bu kurallı kampanya geçerli olmasın
                    if (! $user) {
                        return [false, 0.0, 'user_required'];
                    }

                    $operator = $rule['operator'] ?? 'eq';
                    $value    = (int) ($rule['value'] ?? 0);

                    $orderCount = $this->getEffectiveOrderCount($user);

                    $ok = match ($operator) {
                        'eq'  => $orderCount === $value,
                        'gte' => $orderCount >= $value,
                        'lte' => $orderCount <= $value,
                        default => true,
                    };

                    if (! $ok) {
                        return [false, 0.0, 'user_order_count'];
                    }
                    break;

                default:
                    // bilinmeyen kural tiplerini yok sayıyoruz
                    break;
            }
        }

        // Hedef ürün tiplerine göre indirim tabanı
        if ($targetProductTypes && is_array($targetProductTypes) && ! empty($targetProductTypes)) {
            $discountBaseAmount = 0.0;

            foreach ($cartItems as $ci) {
                $pt = $ci['product_type'] ?? null;

                if ($pt === 'hotel_room') {
                    $normalized = 'hotel';
                } else {
                    $normalized = $pt;
                }

                if ($normalized && in_array($normalized, $targetProductTypes, true)) {
                    $discountBaseAmount += (float) ($ci['amount'] ?? 0);
                }
            }

            if ($discountBaseAmount <= 0) {
                return [false, 0.0, 'no_target_products_in_cart'];
            }
        }

        // 3) İndirim hesabı
        $discount = 0.0;

        if ($vm['discount_type'] === 'percent' && $vm['percent_value'] !== null) {
            $percent = (float) $vm['percent_value'];
            if ($percent > 0) {
                $raw = $discountBaseAmount * ($percent / 100);

                $max = $vm['max_discount_value'] ?? null;
                if ($max !== null && $max > 0) {
                    $raw = min($raw, (float) $max);
                }

                $discount = min($raw, $discountBaseAmount);
            }
        } elseif ($vm['discount_type'] === 'amount' && $vm['amount_value'] !== null) {
            $amount   = (float) $vm['amount_value'];
            $discount = min($amount, $discountBaseAmount);
        }

        $isApplicable = $discount > 0;

        return [$isApplicable, $discount, $isApplicable ? null : 'no_discount'];
    }

    /**
     * Kullanıcının "geçerli" sipariş adedi.
     * Burada paid + cancelled olmayan siparişleri sayıyoruz.
     */
    protected function getEffectiveOrderCount(User $user): int
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->where('payment_status', 'paid')
            ->where('status', '!=', 'cancelled')
            ->count();
    }
}
