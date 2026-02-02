<?php

namespace App\Services;

use App\Models\Tour;
use App\Support\Currency\CurrencyContext;
use App\Support\Helpers\I18nHelper;
use App\Support\Helpers\LocaleHelper;
use Illuminate\Http\Request;

class TourPriceQuoteService
{
    /**
     * @return array{
     *   ok: bool,
     *   err: ?string,
     *   currency: ?string,
     *   amount: float,
     *   breakdown: array,
     *   snapshot: array
     * }
     */
    public function quote(
        int $tourId,
        string $dateYmd,
        int $adults,
        int $children,
        int $infants,
        Request $request
    ): array {
        $currency = CurrencyContext::code($request);
        $currency = strtoupper(trim((string) $currency));

        if ($currency === '') {
            return [
                'ok'        => false,
                'err'       => 'msg.err.tour.currency_missing',
                'currency'  => null,
                'amount'    => 0.0,
                'breakdown' => [],
                'snapshot'  => [],
            ];
        }

        if ($adults < 1) {
            return [
                'ok'        => false,
                'err'       => 'msg.info.price_not_found',
                'currency'  => $currency,
                'amount'    => 0.0,
                'breakdown' => [],
                'snapshot'  => [],
            ];
        }

        $tour = Tour::query()
            ->where('is_active', true)
            ->with(['category', 'media'])
            ->find($tourId);

        if (! $tour) {
            return [
                'ok'        => false,
                'err'       => 'msg.err.tour.not_found',
                'currency'  => $currency,
                'amount'    => 0.0,
                'breakdown' => [],
                'snapshot'  => [],
            ];
        }

        $prices = $tour->prices;

        if (! is_array($prices) || ! isset($prices[$currency]) || ! is_array($prices[$currency])) {
            return [
                'ok'        => false,
                'err'       => 'msg.info.price_not_found',
                'currency'  => $currency,
                'amount'    => 0.0,
                'breakdown' => [],
                'snapshot'  => [],
            ];
        }

        $cfg = $prices[$currency];

        $adultUnit  = (isset($cfg['adult'])  && is_numeric($cfg['adult']))  ? (float) $cfg['adult']  : null;
        $childUnit  = (isset($cfg['child'])  && is_numeric($cfg['child']))  ? (float) $cfg['child']  : 0.0;
        $infantUnit = (isset($cfg['infant']) && is_numeric($cfg['infant'])) ? (float) $cfg['infant'] : 0.0;

        if ($adultUnit === null || $adultUnit <= 0) {
            return [
                'ok'        => false,
                'err'       => 'msg.info.price_not_found',
                'currency'  => $currency,
                'amount'    => 0.0,
                'breakdown' => [],
                'snapshot'  => [],
            ];
        }

        $total =
            ($adults * $adultUnit) +
            ($children * $childUnit) +
            ($infants * $infantUnit);

        if ($total <= 0) {
            return [
                'ok'        => false,
                'err'       => 'msg.info.price_not_found',
                'currency'  => $currency,
                'amount'    => 0.0,
                'breakdown' => [],
                'snapshot'  => [],
            ];
        }

        $ui   = app()->getLocale();
        $base = LocaleHelper::defaultCode();

        $tourName = I18nHelper::scalar($tour->name, $ui, $base);

        $categoryName = null;
        if ($tour->category) {
            $categoryName = I18nHelper::scalar($tour->category->name, $ui, $base);
        }

        $snapshot = [
            'tour_id'     => $tourId,
            'date'        => $dateYmd,
            'adults'      => $adults,
            'children'    => $children,
            'infants'     => $infants,

            'currency'    => $currency,
            'price_total' => $total,

            'tour_name'     => $tourName,
            'category_name' => $categoryName,
        ];

        $img = $tour->cover_image ?? null;
        if (is_array($img) && ($img['exists'] ?? false)) {
            $snapshot['cover_image'] = $img;
        }

        return [
            'ok'        => true,
            'err'       => null,
            'currency'  => $currency,
            'amount'    => (float) $total,
            'breakdown' => [
                'adult_unit'  => $adultUnit,
                'child_unit'  => $childUnit,
                'infant_unit' => $infantUnit,
                'adults'      => $adults,
                'children'    => $children,
                'infants'     => $infants,
            ],
            'snapshot'  => $snapshot,
        ];
    }
}
