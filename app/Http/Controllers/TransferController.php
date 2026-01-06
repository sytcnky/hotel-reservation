<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\StaticPage;
use App\Models\TransferRoute;
use App\Models\TransferVehicle;
use App\Support\Helpers\CurrencyHelper;
use App\Support\Helpers\ImageHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        // -------------------------------------------------
        // Static Page (Transfer)
        // -------------------------------------------------
        $page = StaticPage::query()
            ->where('key', 'transfer_page')
            ->where('is_active', true)
            ->firstOrFail();

        $base = config('app.locale', 'tr');
        $loc  = app()->getLocale();
        $c    = $page->content ?? [];

        $pickLocale = function ($map) use ($loc, $base) {
            if (! is_array($map)) return null;
            return $map[$loc] ?? $map[$base] ?? null;
        };

        // -------------------------------------------------
        // Transfer Search / Offer
        // -------------------------------------------------
        $transferOffer = null;

        $direction      = $request->get('direction', 'oneway') === 'roundtrip' ? 'roundtrip' : 'oneway';
        $fromId         = $request->integer('from_location_id');
        $toId           = $request->integer('to_location_id');
        $departureDate  = $request->input('departure_date');
        $returnDate     = $request->input('return_date');

        $adults   = max(0, $request->integer('adults', 2));
        $children = max(0, $request->integer('children', 0));
        $infants  = max(0, $request->integer('infants', 0));

        $hasSearch = $request->has('from_location_id')
            || $request->has('to_location_id')
            || $request->has('departure_date')
            || $request->has('adults');

        $totalPassengers = $adults + $children + $infants;

        $isInputValid =
            $fromId && $toId && $fromId !== $toId &&
            $departureDate &&
            $adults >= 1 &&
            $totalPassengers > 0 &&
            ($direction === 'oneway' || ($direction === 'roundtrip' && $returnDate));

        if ($hasSearch && $isInputValid) {
            $route = $this->findActiveRoute($fromId, $toId);

            if ($route) {
                $vehicle = $this->findBestVehicle($totalPassengers);

                if ($vehicle) {
                    $currentCurrency = CurrencyHelper::currentCode();

                    $pricing = $this->calculatePrice(
                        $route,
                        $direction,
                        $adults,
                        $children,
                        $infants,
                        $currentCurrency
                    );

                    if ($pricing) {
                        $mediaItems = $vehicle->getMedia('gallery');

                        $vehicleGallery = $mediaItems
                            ->map(fn ($media) => ImageHelper::normalize($media))
                            ->values()
                            ->all();

                        if (empty($vehicleGallery)) {
                            $placeholder    = ImageHelper::normalize(null);
                            $vehicleGallery = [$placeholder];
                        }

                        $vehicleImage = $vehicleGallery[0]['thumb'] ?? null;

                        $transferOffer = [
                            'route_id'               => $route->id,
                            'from_location_id'       => $fromId,
                            'to_location_id'         => $toId,
                            'direction'              => $direction,
                            'departure_date'         => $departureDate,
                            'return_date'            => $returnDate,
                            'adults'                 => $adults,
                            'children'               => $children,
                            'infants'                => $infants,
                            'estimated_duration_min' => $route->duration_minutes,

                            'vehicle_id'             => $vehicle->id,
                            'vehicle_name'           => $this->localizeJson($vehicle->name),
                            'capacity_total'         => $vehicle->capacity_total,

                            'price_total'            => $pricing['total'],
                            'currency'               => $pricing['currency'],

                            'vehicle_gallery'        => $vehicleGallery,
                            'vehicle_image'          => $vehicleImage,
                        ];
                    }
                }
            }
        }

        $locations = $this->getLocationsForSelect();

        return view('pages.transfer.index', [
            'page'          => $page,
            'c'             => $c,
            'pickLocale'    => $pickLocale,

            'transferOffer' => $transferOffer,
            'locations'     => $locations,
            'hasSearch'     => $hasSearch,
        ]);
    }

    private function findActiveRoute(int $fromId, int $toId): ?TransferRoute
    {
        return TransferRoute::query()
            ->where('is_active', true)
            ->where(function ($query) use ($fromId, $toId) {
                $query
                    ->where(function ($q) use ($fromId, $toId) {
                        $q->where('from_location_id', $fromId)
                            ->where('to_location_id', $toId);
                    })
                    ->orWhere(function ($q) use ($fromId, $toId) {
                        $q->where('from_location_id', $toId)
                            ->where('to_location_id', $fromId);
                    });
            })
            ->first();
    }

    private function findBestVehicle(int $totalPassengers): ?TransferVehicle
    {
        if ($totalPassengers <= 0) {
            return null;
        }

        return TransferVehicle::query()
            ->where('is_active', true)
            ->where('capacity_total', '>=', $totalPassengers)
            ->orderBy('capacity_total')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    private function calculatePrice(
        TransferRoute $route,
        string $direction,
        int $adults,
        int $children,
        int $infants,
        string $preferredCurrency
    ): ?array {
        $prices = $route->prices;

        if (! is_array($prices) || empty($prices)) {
            return null;
        }

        $preferredCurrency = strtoupper($preferredCurrency);

        if (array_key_exists($preferredCurrency, $prices)) {
            $currency = $preferredCurrency;
        } elseif (array_key_exists('TRY', $prices)) {
            $currency = 'TRY';
        } else {
            $currency = array_key_first($prices);
        }

        $cfg = $prices[$currency] ?? null;
        if (! is_array($cfg)) {
            return null;
        }

        $adultPrice  = (float) ($cfg['adult']  ?? 0);
        $childPrice  = (float) ($cfg['child']  ?? 0);
        $infantPrice = (float) ($cfg['infant'] ?? 0);

        $oneWay = ($adults * $adultPrice)
            + ($children * $childPrice)
            + ($infants * $infantPrice);

        if ($oneWay <= 0) {
            return null;
        }

        $total = $direction === 'roundtrip'
            ? $oneWay * 2
            : $oneWay;

        return [
            'currency' => $currency,
            'total'    => $total,
        ];
    }

    private function getLocationsForSelect(): array
    {
        return Cache::remember('transfer_locations_for_select', 600, function () {
            $routes = TransferRoute::query()
                ->where('is_active', true)
                ->get(['from_location_id', 'to_location_id']);

            $locationIds = $routes
                ->flatMap(fn ($r) => [$r->from_location_id, $r->to_location_id])
                ->unique()
                ->values();

            if ($locationIds->isEmpty()) {
                return [];
            }

            $locations = Location::query()
                ->whereIn('id', $locationIds)
                ->orderBy('id')
                ->get(['id', 'name']);

            return $locations
                ->map(function (Location $location) {
                    $label = $this->localizeJson($location->name ?? []);

                    return [
                        'id'    => $location->id,
                        'label' => $label !== '' ? $label : ('#' . $location->id),
                    ];
                })
                ->values()
                ->all();
        });
    }

    private function localizeJson($value): string
    {
        if (! is_array($value)) {
            return (string) $value;
        }

        $locale   = app()->getLocale();
        $fallback = config('app.fallback_locale', 'en');

        if (! empty($value[$locale])) {
            return (string) $value[$locale];
        }

        if (! empty($value[$fallback])) {
            return (string) $value[$fallback];
        }

        foreach ($value as $v) {
            if (! empty($v)) {
                return (string) $v;
            }
        }

        return '';
    }
}
