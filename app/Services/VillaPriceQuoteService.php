<?php

namespace App\Services;

use App\Models\Villa;
use App\Support\Currency\CurrencyContext;
use App\Support\Helpers\I18nHelper;
use App\Support\Helpers\LocaleHelper;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class VillaPriceQuoteService
{
    public function __construct(
        private readonly VillaRateRuleSelector $ruleSelector
    ) {
    }

    /**
     * @return array{
     *   ok: bool,
     *   err: ?string,
     *   currency: ?string,
     *   amount: float,
     *   nights: int,
     *   nightly: float,
     *   total: float,
     *   prepayment: float,
     *   exponent: int,
     *   snapshot: array
     * }
     */
    public function quote(Villa $villa, string $checkinYmd, string $checkoutYmd, Request $request): array
    {
        $currency = CurrencyContext::code($request);
        $currency = strtoupper(trim((string) $currency));

        if ($currency === '') {
            return [
                'ok'         => false,
                'err'        => 'msg.err.villa.currency_missing',
                'currency'   => null,
                'amount'     => 0.0,
                'nights'     => 0,
                'nightly'    => 0.0,
                'total'      => 0.0,
                'prepayment' => 0.0,
                'exponent'   => 2,
                'snapshot'   => [],
            ];
        }

        try {
            $in  = CarbonImmutable::createFromFormat('Y-m-d', $checkinYmd)->startOfDay();
            $out = CarbonImmutable::createFromFormat('Y-m-d', $checkoutYmd)->startOfDay();
        } catch (\Throwable) {
            return [
                'ok'         => false,
                'err'        => 'msg.err.villa.dates_invalid',
                'currency'   => $currency,
                'amount'     => 0.0,
                'nights'     => 0,
                'nightly'    => 0.0,
                'total'      => 0.0,
                'prepayment' => 0.0,
                'exponent'   => 2,
                'snapshot'   => [],
            ];
        }

        $nights = (int) $in->diffInDays($out);
        if ($nights < 1) {
            return [
                'ok'         => false,
                'err'        => 'msg.err.villa.dates_invalid',
                'currency'   => $currency,
                'amount'     => 0.0,
                'nights'     => 0,
                'nightly'    => 0.0,
                'total'      => 0.0,
                'prepayment' => 0.0,
                'exponent'   => 2,
                'snapshot'   => [],
            ];
        }

        $rule = $this->ruleSelector->select($villa, $currency);

        $nightly = ($rule && $rule->amount !== null) ? (float) $rule->amount : 0.0;
        if ($nightly <= 0) {
            return [
                'ok'         => false,
                'err'        => 'msg.info.price_not_found',
                'currency'   => $currency,
                'amount'     => 0.0,
                'nights'     => $nights,
                'nightly'    => 0.0,
                'total'      => 0.0,
                'prepayment' => 0.0,
                'exponent'   => 2,
                'snapshot'   => [],
            ];
        }

        if ($rule) {
            $min = $rule->min_nights;
            if (is_numeric($min)) {
                $min = (int) $min;
                if ($min > 0 && $nights < $min) {
                    return [
                        'ok'         => false,
                        'err'        => 'msg.err.villa.min_nights',
                        'currency'   => $currency,
                        'amount'     => 0.0,
                        'nights'     => $nights,
                        'nightly'    => 0.0,
                        'total'      => 0.0,
                        'prepayment' => 0.0,
                        'exponent'   => 2,
                        'snapshot'   => [],
                    ];
                }
            }

            $max = $rule->max_nights;
            if (is_numeric($max)) {
                $max = (int) $max;
                if ($max > 0 && $nights > $max) {
                    return [
                        'ok'         => false,
                        'err'        => 'msg.err.villa.max_nights',
                        'currency'   => $currency,
                        'amount'     => 0.0,
                        'nights'     => $nights,
                        'nightly'    => 0.0,
                        'total'      => 0.0,
                        'prepayment' => 0.0,
                        'exponent'   => 2,
                        'snapshot'   => [],
                    ];
                }
            }
        }

        $currencyModel = CurrencyContext::model($request);
        $exp = $currencyModel ? (int) $currencyModel->exponent : 2;
        if ($exp < 0) {
            $exp = 0;
        }

        $totalRaw = $nightly * $nights;
        $total = round($totalRaw, $exp, PHP_ROUND_HALF_UP);

        $rate = (float) ($villa->prepayment_rate ?? 0);
        $prepaymentRaw = $rate > 0 ? ($total * ($rate / 100)) : 0.0;
        $prepayment = round($prepaymentRaw, $exp, PHP_ROUND_HALF_UP);

        $nightly = round($nightly, $exp, PHP_ROUND_HALF_UP);

        if ($total <= 0 || $prepayment <= 0) {
            return [
                'ok'         => false,
                'err'        => 'msg.info.price_not_found',
                'currency'   => $currency,
                'amount'     => 0.0,
                'nights'     => $nights,
                'nightly'    => $nightly,
                'total'      => $total,
                'prepayment' => $prepayment,
                'exponent'   => $exp,
                'snapshot'   => [],
            ];
        }

        $ui   = app()->getLocale();
        $base = LocaleHelper::defaultCode();

        $villaName = I18nHelper::scalar($villa->name, $ui, $base);

        $area     = $villa->location?->name;
        $district = $villa->location?->parent?->name;
        $locationLabel = implode(', ', array_filter([$area, $district]));

        $snapshot = [
            'villa_id'          => (int) $villa->id,
            'villa_name'        => $villaName,
            'location_label'    => $locationLabel !== '' ? $locationLabel : null,

            'checkin'           => $checkinYmd,
            'checkout'          => $checkoutYmd,

            'currency'          => $currency,
            'nights'            => $nights,
            'price_nightly'     => $nightly,
            'price_total'       => $total,
            'price_prepayment'  => $prepayment,
        ];

        $img = $villa->cover_image ?? null;
        if (is_array($img) && ($img['exists'] ?? false)) {
            $snapshot['villa_image'] = $img;
        }

        return [
            'ok'         => true,
            'err'        => null,
            'currency'   => $currency,
            'amount'     => (float) $prepayment,
            'nights'     => $nights,
            'nightly'    => $nightly,
            'total'      => $total,
            'prepayment' => $prepayment,
            'exponent'   => $exp,
            'snapshot'   => $snapshot,
        ];
    }
}
