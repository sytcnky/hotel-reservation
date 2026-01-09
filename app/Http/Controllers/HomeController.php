<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\StaticPage;
use App\Models\TravelGuide;
use App\Services\CampaignPlacementViewService;
use App\Support\Helpers\I18nHelper;
use App\Support\Helpers\LocaleHelper;

class HomeController extends Controller
{
    public function index(CampaignPlacementViewService $campaignService)
    {
        $page = StaticPage::query()
            ->where('key', 'home_page')
            ->where('is_active', true)
            ->firstOrFail();

        // Locale standardı (tek otorite)
        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();

        $content = $page->content ?? [];

        // -----------------------------
        // Popular Hotels
        // -----------------------------
        $popularHotels = collect();

        $hotelMode = data_get($content, 'popular_hotels.carousel.mode'); // latest | manual | by_location

        $hotelPerPage = (int) (data_get($content, 'popular_hotels.carousel.per_page') ?? 4);
        $hotelTotal   = (int) (data_get($content, 'popular_hotels.carousel.total') ?? $hotelPerPage);
        $hotelLimit   = data_get($content, 'popular_hotels.carousel.limit');

        $hotelTake = is_numeric($hotelLimit)
            ? max(1, (int) $hotelLimit)
            : max(1, (int) $hotelTotal);

        if ($hotelMode === 'manual') {
            $ids = collect((array) data_get($content, 'popular_hotels.carousel.items', []))
                ->pluck('id')
                ->filter(fn ($v) => is_numeric($v))
                ->map(fn ($v) => (int) $v)
                ->values()
                ->take($hotelTake)
                ->all();

            if (! empty($ids)) {
                $popularHotels = Hotel::query()
                    ->where('is_active', true)
                    ->withoutTrashed()
                    ->whereIn('id', $ids)
                    ->get()
                    ->sortBy(fn ($h) => array_search($h->id, $ids, true))
                    ->values();
            }
        } elseif ($hotelMode === 'by_location') {
            $locationId = data_get($content, 'popular_hotels.carousel.location_id');

            if (is_numeric($locationId)) {
                $popularHotels = Hotel::query()
                    ->where('is_active', true)
                    ->withoutTrashed()
                    ->where('location_id', (int) $locationId)
                    ->latest('id')
                    ->take($hotelTake)
                    ->get();
            }
        } else {
            // default: latest
            $popularHotels = Hotel::query()
                ->where('is_active', true)
                ->withoutTrashed()
                ->latest('id')
                ->take($hotelTake)
                ->get();
        }

        // -----------------------------
        // Travel Guides
        // -----------------------------
        $travelGuides = collect();

        $guideMode  = data_get($content, 'travel_guides.grid.mode'); // latest | manual
        $guideLimit = (int) (data_get($content, 'travel_guides.grid.limit') ?? 4);
        $guideTake  = max(1, $guideLimit);

        if ($guideMode === 'manual') {
            $ids = collect((array) data_get($content, 'travel_guides.grid.items', []))
                ->pluck('id')
                ->filter(fn ($v) => is_numeric($v))
                ->map(fn ($v) => (int) $v)
                ->values()
                ->take($guideTake)
                ->all();

            if (! empty($ids)) {
                $travelGuides = TravelGuide::query()
                    ->where('is_active', true)
                    ->withoutTrashed()
                    ->whereIn('id', $ids)
                    ->get()
                    ->sortBy(fn ($g) => array_search($g->id, $ids, true))
                    ->values();
            }
        } else {
            // default: latest
            $travelGuides = TravelGuide::query()
                ->where('is_active', true)
                ->withoutTrashed()
                ->latest('id')
                ->take($guideTake)
                ->get();
        }

        // View’de locale-keyed map pick gerekiyorsa (hard fallback yok)
        $pickLocale = function ($map) use ($uiLocale, $baseLocale) {
            return I18nHelper::scalar($map, $uiLocale, $baseLocale);
        };

        return view('pages.home', [
            'page'         => $page,
            'popularHotels'=> $popularHotels,
            'travelGuides' => $travelGuides,
            'pickLocale'   => $pickLocale,
            'campaigns'    => $campaignService->buildForPlacement('homepage_banner'),
        ]);
    }
}
