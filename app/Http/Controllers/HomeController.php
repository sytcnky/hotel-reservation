<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\StaticPage;
use App\Models\TravelGuide;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Services\CampaignPlacementViewService;
use App\Services\TransferLocationSelectService;
use App\Support\Currency\CurrencyContext;
use App\Support\Helpers\I18nHelper;
use App\Support\Helpers\LocaleHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(
        Request $request,
        CampaignPlacementViewService $campaignService,
        TransferLocationSelectService $locationSelect
    ) {
        $page = StaticPage::query()
            ->where('key', 'home_page')
            ->where('is_active', true)
            ->firstOrFail();

        // Locale standardı (tek otorite)
        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();

        $content = $page->content ?? [];

        // -----------------------------
        // Currency (tek otorite) — home page de min price hesaplayacaksa gerekli
        // -----------------------------
        $currencyCode = CurrencyContext::code($request);
        $currencyCode = $currencyCode ? strtoupper($currencyCode) : null;

        $currencyId = CurrencyContext::id($request);

        // Listing servisindeki AC-2 + AC-7 ile aynı "base validity rule"
        // Not: currencyId yoksa min fiyatlar null kalır (UI zaten "Fiyat bulunamadı" gösterebilir).
        $validRule = function ($q) use ($currencyId) {
            if (! $currencyId) {
                // currency yoksa deterministik: hiçbir kuralı valid sayma
                $q->whereRaw('1=0');

                return;
            }

            $q->where('rrr.is_active', true)
                ->where('rrr.closed', false)
                ->where('rrr.currency_id', $currencyId)
                ->where(function ($qq) {
                    $qq->whereNull('rrr.allotment')
                        ->orWhere('rrr.allotment', '>', 0);
                });
        };

        $applyMinPriceJoin = function ($hotelsQuery) use ($validRule) {
            // base set: bu query'nin döndüreceği otel id'leri ile sınırla
            $baseHotelsSub = (clone $hotelsQuery)->select('hotels.id');

            $minPriceSub = DB::table('room_rate_rules as rrr')
                ->join('rooms as r', 'r.id', '=', 'rrr.room_id')
                ->selectRaw('DISTINCT ON (r.hotel_id) r.hotel_id, rrr.amount as from_price_amount, rrr.price_type as from_price_type')
                ->where('r.is_active', true)
                ->whereIn('r.hotel_id', $baseHotelsSub)
                ->where(function ($q) use ($validRule) {
                    $validRule($q);
                })
                ->orderBy('r.hotel_id')
                ->orderBy('rrr.amount', 'asc')
                ->orderBy('rrr.id', 'asc');

            return $hotelsQuery
                ->leftJoinSub($minPriceSub, 'mp', function ($join) {
                    $join->on('mp.hotel_id', '=', 'hotels.id');
                })
                ->addSelect('hotels.*')
                ->addSelect([
                    'from_price_amount' => 'mp.from_price_amount',
                    'from_price_type'   => 'mp.from_price_type',
                ]);
        };

        // -----------------------------
        // Popular Hotels
        // -----------------------------
        $popularHotels = collect();

        $hotelMode = data_get($content, 'popular_hotels.carousel.mode'); // latest | manual | by_location

        // total kaldırıldı varsayımıyla: tek otorite "limit"
        $hotelLimit = data_get($content, 'popular_hotels.carousel.limit');
        $hotelTake  = is_numeric($hotelLimit) ? max(1, (int) $hotelLimit) : 1;

        $basePopularHotelsQuery = Hotel::query()
            ->where('hotels.is_active', true)
            ->withoutTrashed()
            ->with([
                'location.parent.parent',
                'featureGroups.facilities',
                'starRating',
                'media',
            ]);

        if ($hotelMode === 'manual') {
            $ids = collect((array) data_get($content, 'popular_hotels.carousel.items', []))
                ->pluck('id')
                ->filter(fn ($v) => is_numeric($v))
                ->map(fn ($v) => (int) $v)
                ->values()
                ->take($hotelTake)
                ->all();

            if (! empty($ids)) {
                $q = (clone $basePopularHotelsQuery)
                    ->whereIn('hotels.id', $ids);

                $q = $applyMinPriceJoin($q);

                $popularHotels = $q->get()
                    ->sortBy(fn ($h) => array_search($h->id, $ids, true))
                    ->values();
            }
        } elseif ($hotelMode === 'by_location') {
            $locationId = data_get($content, 'popular_hotels.carousel.location_id');

            if (is_numeric($locationId)) {
                $q = (clone $basePopularHotelsQuery)
                    ->where('hotels.location_id', (int) $locationId)
                    ->latest('hotels.id')
                    ->take($hotelTake);

                $q = $applyMinPriceJoin($q);

                $popularHotels = $q->get();
            }
        } else {
            // default: latest
            $q = (clone $basePopularHotelsQuery)
                ->latest('hotels.id')
                ->take($hotelTake);

            $q = $applyMinPriceJoin($q);

            $popularHotels = $q->get();
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
                    ->with('media')
                    ->get()
                    ->sortBy(fn ($g) => array_search($g->id, $ids, true))
                    ->values();
            }
        } else {
            // default: latest
            $travelGuides = TravelGuide::query()
                ->where('is_active', true)
                ->withoutTrashed()
                ->with('media')
                ->latest('id')
                ->take($guideTake)
                ->get();
        }

        // View’de locale-keyed map pick gerekiyorsa (hard fallback yok)
        $pickLocale = function ($map) use ($uiLocale, $baseLocale) {
            return I18nHelper::scalar($map, $uiLocale, $baseLocale);
        };

        $transferLocations = $locationSelect->getOptions();

        // Home tab sadece kategori istiyor ama "boş olmayanlar" filtresi için turlar lazım.
        $toursForCat = Tour::query()
            ->with(['category'])
            ->where('is_active', true)
            ->get()
            ->map(fn (Tour $t) => [
                'category'      => $t->category?->name_l,
                'category_slug' => $t->category?->slug_l,
            ]);

// Kategoriler (boş olmayanlar)
        $tourCategories = TourCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(function ($cat) use ($toursForCat) {
                return $toursForCat->contains(fn ($t) => $t['category'] === $cat->name_l);
            })
            ->map(fn ($cat) => [
                'id'   => $cat->id,
                'name' => $cat->name_l,
                'slug' => $cat->slug_l,
            ])
            ->values();

        return view('pages.home', [
            'page'              => $page,
            'popularHotels'     => $popularHotels,
            'travelGuides'      => $travelGuides,
            'pickLocale'        => $pickLocale,
            'campaigns'         => $campaignService->buildForPlacement('homepage_banner'),
            'currencyCode'      => $currencyCode,

            // Home transfer tab için
            'transferLocations' => $transferLocations,

            'categories' => $tourCategories,
        ]);
    }

}
