<?php

namespace App\Http\Controllers;

use App\Models\StaticPage;
use App\Models\TransferRoute;
use App\Models\TransferRouteVehiclePrice;
use App\Models\TransferVehicle;
use App\Services\TransferLocationSelectService;
use App\Support\Currency\CurrencyContext;
use App\Support\Helpers\I18nHelper;
use App\Support\Helpers\LocaleHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransferController extends Controller
{
    public function index(Request $request, TransferLocationSelectService $locationSelect)
    {
        // -------------------------------------------------
        // Static Page (Transfer)
        // -------------------------------------------------
        $page = StaticPage::query()
            ->where('key', 'transfer_page')
            ->where('is_active', true)
            ->firstOrFail();

        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();
        $c          = $page->content ?? [];

        $pickLocale = function ($map) use ($uiLocale, $baseLocale) {
            if (! is_array($map)) {
                return null;
            }

            return $map[$uiLocale] ?? $map[$baseLocale] ?? null;
        };

        // -------------------------------------------------
        // Transfer Search / Offer
        // -------------------------------------------------
        $transferOffer = null;

        $direction = $request->get('direction', 'oneway') === 'roundtrip' ? 'roundtrip' : 'oneway';
        $fromId    = $request->integer('from_location_id');
        $toId      = $request->integer('to_location_id');

        // STRICT: search GET dates must be Y-m-d. No legacy parse.
        $departureDateRaw = $request->input('departure_date');
        $returnDateRaw    = $request->input('return_date');

        $departureDate = self::normalizeYmdDate($departureDateRaw);
        $returnDate    = $direction === 'roundtrip'
            ? self::normalizeYmdDate($returnDateRaw)
            : null;

        $adults   = max(0, $request->integer('adults', 2));
        $children = max(0, $request->integer('children', 0));
        $infants  = max(0, $request->integer('infants', 0));

        $hasSearch = $request->has('from_location_id')
            || $request->has('to_location_id')
            || $request->has('departure_date')
            || $request->has('adults');

        $totalPassengers = $adults + $children + $infants;

        // Invalid date format => no results (no error), and NO legacy parse.
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
                    $currentCurrency = CurrencyContext::code($request);

                    $pricing = $this->calculatePrice(
                        $route,
                        $vehicle,
                        $direction,
                        $currentCurrency
                    );

                    if ($pricing) {
                        // Cover tek otorite: accessor zaten normalize + placeholder döner.
                        $cover = $vehicle->cover_image;

                        $transferOffer = [
                            'route_id'               => $route->id,
                            'from_location_id'       => $fromId,
                            'to_location_id'         => $toId,
                            'direction'              => $direction,
                            // STRICT: Offer dates always Y-m-d (normalized)
                            'departure_date'         => $departureDate,
                            'return_date'            => $returnDate,
                            'adults'                 => $adults,
                            'children'               => $children,
                            'infants'                => $infants,
                            'estimated_duration_min' => $route->duration_minutes,

                            'vehicle_id'             => $vehicle->id,
                            'vehicle_name'           => I18nHelper::scalar($vehicle->name, $uiLocale, $baseLocale),
                            'vehicle_description'    => I18nHelper::scalar($vehicle->description, $uiLocale, $baseLocale),
                            'capacity_total'         => $vehicle->capacity_total,

                            'price_total'            => $pricing['total'],
                            'currency'               => $pricing['currency'],

                            'vehicle_cover'          => $cover,
                            'vehicle_gallery'        => $vehicle->gallery_images,
                        ];
                    }
                }
            }
        }

        $locations = $locationSelect->getOptions();

        return view('pages.transfer.index', [
            'page'          => $page,
            'c'             => $c,
            'pickLocale'    => $pickLocale,

            'transferOffer' => $transferOffer,
            'locations'     => $locations,
            'hasSearch'     => $hasSearch,
        ]);
    }

    /**
     * Strict Y-m-d normalize. Returns normalized Y-m-d or null.
     * No legacy parse.
     */
    private static function normalizeYmdDate($value): ?string
    {
        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        // Fast guard: must look like YYYY-MM-DD
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            return null;
        }

        try {
            $dt = Carbon::createFromFormat('Y-m-d', $raw)->startOfDay();
        } catch (\Throwable) {
            return null;
        }

        // createFromFormat can still produce a Carbon even when parts overflow;
        // ensure exact match after re-format.
        $normalized = $dt->format('Y-m-d');
        if ($normalized !== $raw) {
            return null;
        }

        return $normalized;
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
            ->with('media') // eager-load for accessors (cover_image / gallery_images)
            ->where('is_active', true)
            ->where('capacity_total', '>=', $totalPassengers)
            ->orderBy('capacity_total')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    /**
     * Araç başı fiyat hesaplama (yeni sistem).
     *
     * - Fallback yok.
     * - preferredCurrency null/boş ise null.
     * - Pivot kaydı yoksa veya currency fiyatı yoksa null.
     */
    private function calculatePrice(
        TransferRoute $route,
        TransferVehicle $vehicle,
        string $direction,
        ?string $preferredCurrency
    ): ?array {
        $preferredCurrency = strtoupper(trim((string) $preferredCurrency));
        if ($preferredCurrency === '') {
            return null;
        }

        $row = TransferRouteVehiclePrice::query()
            ->whereNull('deleted_at')
            ->where('transfer_route_id', $route->id)
            ->where('transfer_vehicle_id', $vehicle->id)
            ->where('is_active', true)
            ->first();

        if (! $row) {
            return null;
        }

        $prices = $row->prices;
        if (! is_array($prices) || empty($prices)) {
            return null;
        }

        if (! array_key_exists($preferredCurrency, $prices)) {
            return null;
        }

        $oneWay = (float) ($prices[$preferredCurrency] ?? 0);
        if ($oneWay <= 0) {
            return null;
        }

        $total = $direction === 'roundtrip'
            ? $oneWay * 2
            : $oneWay;

        return [
            'currency' => $preferredCurrency,
            'total'    => $total,
        ];
    }

    private function localizeJson($value): string
    {
        if (! is_array($value)) {
            return (string) $value;
        }

        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();

        return I18nHelper::scalar($value, $uiLocale, $baseLocale) ?? '';
    }
}
