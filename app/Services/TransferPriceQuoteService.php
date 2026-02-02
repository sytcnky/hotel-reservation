<?php

namespace App\Services;

use App\Models\TransferRoute;
use App\Models\TransferRouteVehiclePrice;
use App\Models\TransferVehicle;
use App\Support\Currency\CurrencyContext;
use App\Support\Helpers\I18nHelper;
use Illuminate\Http\Request;

class TransferPriceQuoteService
{
    /**
     * @return array{
     *   ok: bool,
     *   err: ?string,
     *   currency: ?string,
     *   amount: float,
     *   direction: string,
     *   breakdown: array,
     *   snapshot: array
     * }
     */
    public function quote(
        int $routeId,
        int $vehicleId,
        string $direction,
        Request $request
    ): array {
        $currency = CurrencyContext::code($request);
        $currency = strtoupper(trim((string) $currency));

        if ($currency === '') {
            return [
                'ok'        => false,
                'err'       => 'msg.err.transfer.currency_missing',
                'currency'  => null,
                'amount'    => 0.0,
                'direction' => $direction,
                'breakdown' => [],
                'snapshot'  => [],
            ];
        }

        $route = TransferRoute::query()
            ->with(['from.parent', 'to.parent'])
            ->find($routeId);

        if (! $route) {
            return [
                'ok'        => false,
                'err'       => 'msg.err.transfer.route_not_found',
                'currency'  => $currency,
                'amount'    => 0.0,
                'direction' => $direction,
                'breakdown' => [],
                'snapshot'  => [],
            ];
        }

        $vehicle = TransferVehicle::query()
            ->with('media')
            ->find($vehicleId);

        if (! $vehicle) {
            return [
                'ok'        => false,
                'err'       => 'msg.err.transfer.vehicle_not_found',
                'currency'  => $currency,
                'amount'    => 0.0,
                'direction' => $direction,
                'breakdown' => [],
                'snapshot'  => [],
            ];
        }

        $row = TransferRouteVehiclePrice::query()
            ->where('transfer_route_id', $routeId)
            ->where('transfer_vehicle_id', $vehicleId)
            ->where('is_active', true)
            ->first();

        if (! $row) {
            return [
                'ok'        => false,
                'err'       => 'msg.info.price_not_found',
                'currency'  => $currency,
                'amount'    => 0.0,
                'direction' => $direction,
                'breakdown' => [],
                'snapshot'  => [],
            ];
        }

        $prices = $row->prices;
        if (! is_array($prices) || ! array_key_exists($currency, $prices)) {
            return [
                'ok'        => false,
                'err'       => 'msg.info.price_not_found',
                'currency'  => $currency,
                'amount'    => 0.0,
                'direction' => $direction,
                'breakdown' => [],
                'snapshot'  => [],
            ];
        }

        $oneWay = (float) ($prices[$currency] ?? 0);
        if ($oneWay <= 0) {
            return [
                'ok'        => false,
                'err'       => 'msg.info.price_not_found',
                'currency'  => $currency,
                'amount'    => 0.0,
                'direction' => $direction,
                'breakdown' => [],
                'snapshot'  => [],
            ];
        }

        $dir = $direction === 'roundtrip' ? 'roundtrip' : 'oneway';
        $amount = $dir === 'roundtrip' ? $oneWay * 2 : $oneWay;

        $fromArea     = $route->from?->name;
        $fromDistrict = $route->from?->parent?->name;

        $toArea     = $route->to?->name;
        $toDistrict = $route->to?->parent?->name;

        $fromLabel = implode(', ', array_filter([$fromArea, $fromDistrict]));
        $toLabel   = implode(', ', array_filter([$toArea, $toDistrict]));

        $ui   = app()->getLocale();
        $base = \App\Support\Helpers\LocaleHelper::defaultCode();
        $vehicleName = I18nHelper::scalar($vehicle->name, $ui, $base);

        $snapshot = [
            'route_id'      => $routeId,
            'vehicle_id'    => $vehicleId,
            'direction'     => $dir,
            'currency'      => $currency,
            'price_total'   => $amount,

            'from_label'    => $fromLabel,
            'to_label'      => $toLabel,
            'vehicle_name'  => $vehicleName,
        ];

        $img = $vehicle->cover_image ?? null;
        if (is_array($img) && ($img['exists'] ?? false)) {
            $snapshot['vehicle_cover'] = $img;
        }

        return [
            'ok'        => true,
            'err'       => null,
            'currency'  => $currency,
            'amount'    => $amount,
            'direction' => $dir,
            'breakdown' => [
                'oneway' => $oneWay,
                'mult'   => $dir === 'roundtrip' ? 2 : 1,
            ],
            'snapshot'  => $snapshot,
        ];
    }
}
