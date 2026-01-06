<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TravelGuide;
use App\Models\Villa;
use App\Support\Helpers\CurrencyHelper;
use Illuminate\Http\Request;
use App\Models\StaticPage;

class TravelGuideController extends Controller
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();

        $page = StaticPage::query()
            ->where('key', 'travel_guide_page')
            ->where('is_active', true)
            ->firstOrFail();

        $c   = $page->content ?? [];
        $loc = app()->getLocale();

        $guides = TravelGuide::query()
            ->where('is_active', true)
            ->orderByDesc('published_at')
            ->orderBy('sort_order')
            ->with('media') // cover_image accessor için
            ->get();

        return view('pages.guides.index', [
            'guides' => $guides,
            'locale' => $locale,
            'page' => $page,
            'c'    => $c,
            'loc'  => $loc,
        ]);
    }

    public function show(Request $request, string $slug)
    {
        $locale = app()->getLocale();

        $guide = TravelGuide::query()
            ->where('is_active', true)
            ->whereRaw("slug->>? = ?", [$locale, $slug])
            ->with([
                'media',                // guide cover
                'blocks.media',         // block image
                'blocks',               // blocks data
            ])
            ->firstOrFail();

        // Recommendation block’larından hotel/villa id’lerini topla
        $hotelIds = [];
        $villaIds = [];

        foreach ($guide->blocks as $b) {
            if (($b->type ?? null) !== 'recommendation') {
                continue;
            }

            $ptype = data_get($b->data, 'product_type');
            $pid   = (int) data_get($b->data, 'product_id');

            if (! $pid) {
                continue;
            }

            if ($ptype === 'hotel') {
                $hotelIds[] = $pid;
            } elseif ($ptype === 'villa') {
                $villaIds[] = $pid;
            }
        }

        $hotelIds = array_values(array_unique($hotelIds));
        $villaIds = array_values(array_unique($villaIds));

        $hotelsById = collect();
        $villasById = collect();

        if (! empty($hotelIds)) {
            $hotelsById = Hotel::query()
                ->whereIn('id', $hotelIds)
                ->where('is_active', true)
                ->with('media') // cover_image accessor
                ->get()
                ->keyBy('id');
        }

        if (! empty($villaIds)) {
            $villasById = Villa::query()
                ->whereIn('id', $villaIds)
                ->where('is_active', true)
                ->with('media')
                ->get()
                ->keyBy('id');

        }

        // Sidebar turları (guide.sidebar_tour_ids sırasını koru)
        $tourIds = array_values(array_filter((array) ($guide->sidebar_tour_ids ?? [])));

        $sidebarTours = collect();

        if (! empty($tourIds)) {
            $tours = Tour::query()
                ->whereIn('id', $tourIds)
                ->where('is_active', true)
                ->with(['media', 'category'])
                ->get()
                ->keyBy('id');

            $ordered = [];
            foreach ($tourIds as $id) {
                if (isset($tours[$id])) {
                    $ordered[] = $tours[$id];
                }
            }

            $sidebarTours = collect($ordered);
        }

        $currencyCode = strtoupper(CurrencyHelper::currentCode());
        $currencyMeta = CurrencyHelper::options()[$currencyCode] ?? ['code' => $currencyCode, 'symbol' => $currencyCode];

        return view('pages.guides.show', [
            'guide' => $guide,
            'locale' => $locale,

            'hotelsById' => $hotelsById,
            'villasById' => $villasById,

            'sidebarTours' => $sidebarTours,

            'currencyCode' => $currencyCode,
            'currencySymbol' => $currencyMeta['symbol'] ?? $currencyCode,
        ]);
    }
}
