<?php

namespace App\Services;

use App\Models\BoardType;
use App\Models\Currency;
use App\Models\Hotel;
use App\Models\HotelCategory;
use App\Models\StaticPage;
use App\Support\Helpers\CurrencyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HotelListingPageService
{
    public function build(Request $request): array
    {
        $locale = app()->getLocale();

        $page = StaticPage::query()
            ->where('key', 'hotel_page')
            ->where('is_active', true)
            ->firstOrFail();

        $c   = $page->content ?? [];
        $loc = $locale;

        // ---- Filters (request) ----
        $categoryId  = $request->query('category_id');
        $boardTypeId = $request->query('board_type_id');

        $cityId     = $request->query('city_id');
        $districtId = $request->query('district_id');
        $areaId     = $request->query('area_id');

        $guests = max(1, (int) $request->query('guests', 2));

        $checkinRaw = $request->query('checkin');
        [$checkin, $checkout] = $this->parseCheckinRange($checkinRaw);

        // ---- Currency ----
        $currencyCode = CurrencyHelper::currentCode();
        $currencyId   = Currency::query()
            ->where('code', strtoupper($currencyCode))
            ->value('id');

        if (! $currencyId) {
            return [
                'hotels' => collect(),
                'page' => $page,
                'c' => $c,
                'loc' => $loc,
                'currencyCode' => strtoupper($currencyCode),

                'categories' => collect(),
                'boardTypes' => collect(),
                'cities' => collect(),
                'districts' => collect(),
                'areas' => collect(),

                'maxGuests' => 1,

                'filters' => [
                    'category_id' => null,
                    'board_type_id' => null,
                    'city_id' => null,
                    'district_id' => null,
                    'area_id' => null,
                    'guests' => $guests,
                    'checkin' => $checkinRaw,
                ],

                'ui' => [
                    'hide_city' => true,
                    'hide_district' => true,
                    'hide_area' => true,
                ],
            ];
        }

        // ---- Location subtree (selected deepest wins: area > district > city) ----
        $selectedLocationId = $areaId ?: ($districtId ?: $cityId);
        $locationIds = $selectedLocationId
            ? $this->resolveLocationSubtreeIds((int) $selectedLocationId)
            : null;

        // ---- Base validity rule (AC-2 + AC-7) ----
        $validRule = function ($q) use ($currencyId) {
            $q->where('rrr.is_active', true)
                ->where('rrr.closed', false)
                ->where('rrr.currency_id', $currencyId)
                ->where(function ($qq) {
                    $qq->whereNull('rrr.allotment')
                        ->orWhere('rrr.allotment', '>', 0);
                });
        };

        // ---- Base hotels subquery (for result set) ----
        $baseHotelsSub = $this->baseHotelsSubquery(
            $currencyId,
            $validRule,
            [
                'category_id' => $categoryId,
                'board_type_id' => $boardTypeId,
                'location_ids' => $locationIds,
                'guests' => $guests,
                'checkin' => $checkin,
                'checkout' => $checkout,
            ]
        );

        // ---- Min price subquery (only within base hotel ids) ----
        $minPriceSub = \DB::table('room_rate_rules as rrr')
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

        // ---- Hotels query ----
        $hotels = Hotel::query()
            ->with([
                'location.parent.parent',
                'featureGroups.facilities',
                'starRating',
                'media',
            ])
            ->joinSub($minPriceSub, 'mp', function ($join) {
                $join->on('mp.hotel_id', '=', 'hotels.id');
            })
            ->where('hotels.is_active', true)
            ->addSelect('hotels.*')
            ->addSelect([
                'from_price_amount' => 'mp.from_price_amount',
                'from_price_type'   => 'mp.from_price_type',
            ])
            ->orderByRaw("hotels.name->>? asc", [$locale])
            ->get();

        // ---- Option datasets (facet-style: exclude self) ----
        $categories = $this->categoryOptions(
            $currencyId,
            $validRule,
            [
                'board_type_id' => $boardTypeId,
                'location_ids' => $locationIds,
                'guests' => $guests,
                'checkin' => $checkin,
                'checkout' => $checkout,
            ]
        );

        $boardTypes = $this->boardTypeOptions(
            $currencyId,
            $validRule,
            [
                'category_id' => $categoryId,
                'location_ids' => $locationIds,
                'guests' => $guests,
                'checkin' => $checkin,
                'checkout' => $checkout,
            ]
        );

        // ---- Location options (3-level: city > district > area) ----
        [$cities, $districts, $areas, $ui] = $this->locationOptions3Level(
            $currencyId,
            $validRule,
            [
                'category_id'   => $categoryId,
                'board_type_id' => $boardTypeId,
                'guests'        => $guests,
                'checkin'       => $checkin,
                'checkout'      => $checkout,

                'city_id'       => $cityId ? (int) $cityId : null,
                'district_id'   => $districtId ? (int) $districtId : null,
                'area_id'       => $areaId ? (int) $areaId : null,
            ]
        );

        // ---- Max guests (UI upper bound) ----
        $maxGuests = (int) \DB::table('rooms as r')
            ->join('hotels as h', 'h.id', '=', 'r.hotel_id')
            ->where('h.is_active', true)
            ->where('r.is_active', true)
            ->whereExists(function ($q) use ($currencyId, $validRule, $categoryId, $boardTypeId, $locationIds, $checkin, $checkout) {
                $q->selectRaw('1')
                    ->from('room_rate_rules as rrr')
                    ->whereColumn('rrr.room_id', 'r.id');

                $validRule($q);

                if ($categoryId) {
                    $q->whereExists(function ($qq) use ($categoryId) {
                        $qq->selectRaw('1')
                            ->from('hotels as hh')
                            ->whereColumn('hh.id', 'r.hotel_id')
                            ->where('hh.hotel_category_id', (int) $categoryId)
                            ->where('hh.is_active', true);
                    });
                }

                if (is_array($locationIds)) {
                    $q->whereExists(function ($qq) use ($locationIds) {
                        $qq->selectRaw('1')
                            ->from('hotels as hh')
                            ->whereColumn('hh.id', 'r.hotel_id')
                            ->whereIn('hh.location_id', $locationIds)
                            ->where('hh.is_active', true);
                    });
                }

                if ($boardTypeId) {
                    $q->where('rrr.board_type_id', (int) $boardTypeId);
                }

                if ($checkin && $checkout) {
                    $rangeStart = $checkin->toDateString();
                    $rangeEnd   = (clone $checkout)->subDay()->toDateString();

                    $this->applyDateOverlap($q, $rangeStart, $rangeEnd);
                }
            })
            ->max(\DB::raw('(COALESCE(r.capacity_adults,0) + COALESCE(r.capacity_children,0))'));

        $maxGuests = max(1, $maxGuests);

        return [
            'hotels' => $hotels,
            'page' => $page,
            'c' => $c,
            'loc' => $loc,
            'currencyCode' => strtoupper($currencyCode),

            'categories' => $categories,
            'boardTypes' => $boardTypes,
            'cities' => $cities,
            'districts' => $districts,
            'areas' => $areas,

            'maxGuests' => $maxGuests,

            'filters' => [
                'category_id' => $categoryId ? (int) $categoryId : null,
                'board_type_id' => $boardTypeId ? (int) $boardTypeId : null,
                // locked (single option) seçimleri burada garanti altına al
                'city_id' => $locked['city_id'],
                'district_id' => $locked['district_id'],
                'area_id' => $locked['area_id'],
                'guests' => $guests,
                'checkin' => $checkinRaw,
            ],

            'ui' => $ui,
        ];
    }

    private function parseCheckinRange(?string $raw): array
    {
        if (! $raw) {
            return [null, null];
        }

        $raw = trim($raw);
        $parts = preg_split('/\s*-\s*/', $raw);

        try {
            if (count($parts) === 1) {
                $start = Carbon::createFromFormat('d.m.Y', $parts[0])->startOfDay();
                $end   = (clone $start)->addDay();
            } else {
                $start = Carbon::createFromFormat('d.m.Y', $parts[0])->startOfDay();
                $end   = Carbon::createFromFormat('d.m.Y', $parts[1])->startOfDay();

                if ($end->lte($start)) {
                    $end = (clone $start)->addDay();
                }
            }

            return [$start, $end];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }

    private function resolveLocationSubtreeIds(int $locationId): array
    {
        $rows = \DB::select(
            "
            WITH RECURSIVE tree AS (
                SELECT id FROM locations WHERE id = ?
                UNION ALL
                SELECT l.id
                FROM locations l
                JOIN tree t ON l.parent_id = t.id
            )
            SELECT id FROM tree
            ",
            [$locationId]
        );

        $ids = collect($rows)->pluck('id')->map(fn ($v) => (int) $v)->values()->all();

        return ! empty($ids) ? $ids : [$locationId];
    }

    private function baseHotelsSubquery(int $currencyId, \Closure $validRule, array $f)
    {
        $categoryId  = $f['category_id'] ?? null;
        $boardTypeId = $f['board_type_id'] ?? null;
        $locationIds = $f['location_ids'] ?? null;
        $guests      = (int) ($f['guests'] ?? 1);
        $checkin     = $f['checkin'] ?? null;
        $checkout    = $f['checkout'] ?? null;

        return \DB::table('hotels as h')
            ->select('h.id')
            ->where('h.is_active', true)
            ->when($categoryId, fn ($q) => $q->where('h.hotel_category_id', (int) $categoryId))
            ->when(is_array($locationIds), fn ($q) => $q->whereIn('h.location_id', $locationIds))
            ->when($guests > 0, function ($q) use ($guests) {
                $q->whereExists(function ($qq) use ($guests) {
                    $qq->selectRaw('1')
                        ->from('rooms as rcap')
                        ->whereColumn('rcap.hotel_id', 'h.id')
                        ->where('rcap.is_active', true)
                        ->whereRaw('(COALESCE(rcap.capacity_adults,0) + COALESCE(rcap.capacity_children,0)) >= ?', [$guests]);
                });
            })
            ->when($boardTypeId, function ($q) use ($boardTypeId, $validRule) {
                $q->whereExists(function ($qq) use ($boardTypeId, $validRule) {
                    $qq->selectRaw('1')
                        ->from('rooms as r')
                        ->join('room_rate_rules as rrr', 'rrr.room_id', '=', 'r.id')
                        ->whereColumn('r.hotel_id', 'h.id')
                        ->where('r.is_active', true)
                        ->where('rrr.board_type_id', (int) $boardTypeId);

                    $validRule($qq);
                });
            })
            ->when($checkin && $checkout, function ($q) use ($checkin, $checkout, $validRule) {
                $rangeStart = $checkin->toDateString();
                $rangeEnd   = (clone $checkout)->subDay()->toDateString();

                $q->whereExists(function ($qq) use ($rangeStart, $rangeEnd, $validRule) {
                    $qq->selectRaw('1')
                        ->from('rooms as r')
                        ->join('room_rate_rules as rrr', 'rrr.room_id', '=', 'r.id')
                        ->whereColumn('r.hotel_id', 'h.id')
                        ->where('r.is_active', true);

                    $validRule($qq);

                    $this->applyDateOverlap($qq, $rangeStart, $rangeEnd);
                });
            });
    }

    private function applyDateOverlap($q, string $rangeStart, string $rangeEnd): void
    {
        $q->where(function ($qq) use ($rangeStart, $rangeEnd) {
            $qq->where(function ($q0) {
                $q0->whereNull('rrr.date_start')
                    ->whereNull('rrr.date_end');
            })
                ->orWhere(function ($q1) use ($rangeStart, $rangeEnd) {
                    $q1->where(function ($a) use ($rangeEnd) {
                        $a->whereNull('rrr.date_start')
                            ->orWhere('rrr.date_start', '<=', $rangeEnd);
                    })
                        ->where(function ($b) use ($rangeStart) {
                            $b->whereNull('rrr.date_end')
                                ->orWhere('rrr.date_end', '>=', $rangeStart);
                        });
                });
        });
    }

    private function categoryOptions(int $currencyId, \Closure $validRule, array $f)
    {
        $baseHotels = $this->baseHotelsSubquery($currencyId, $validRule, [
            'category_id' => null,
            'board_type_id' => $f['board_type_id'] ?? null,
            'location_ids' => $f['location_ids'] ?? null,
            'guests' => $f['guests'] ?? 1,
            'checkin' => $f['checkin'] ?? null,
            'checkout' => $f['checkout'] ?? null,
        ]);

        $usedCategoryIds = \DB::table('hotels as h')
            ->whereIn('h.id', $baseHotels)
            ->pluck('h.hotel_category_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($usedCategoryIds)) {
            return collect();
        }

        return HotelCategory::query()
            ->where('is_active', true)
            ->whereIn('id', $usedCategoryIds)
            ->orderBy('sort_order')
            ->get(['id', 'name']);
    }

    private function boardTypeOptions(int $currencyId, \Closure $validRule, array $f)
    {
        $baseHotels = $this->baseHotelsSubquery($currencyId, $validRule, [
            'category_id' => $f['category_id'] ?? null,
            'board_type_id' => null,
            'location_ids' => $f['location_ids'] ?? null,
            'guests' => $f['guests'] ?? 1,
            'checkin' => $f['checkin'] ?? null,
            'checkout' => $f['checkout'] ?? null,
        ]);

        $usedBoardTypeIds = \DB::table('room_rate_rules as rrr')
            ->join('rooms as r', 'r.id', '=', 'rrr.room_id')
            ->join('hotels as h', 'h.id', '=', 'r.hotel_id')
            ->whereIn('h.id', $baseHotels)
            ->where('r.is_active', true)
            ->where(function ($q) use ($validRule) {
                $validRule($q);
            })
            ->pluck('rrr.board_type_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($usedBoardTypeIds)) {
            return collect();
        }

        return BoardType::query()
            ->where('is_active', true)
            ->whereIn('id', $usedBoardTypeIds)
            ->orderBy('sort_order')
            ->get(['id', 'name']);
    }

    private function locationOptions3Level(int $currencyId, \Closure $validRule, array $f): array
    {
        $baseHotels = $this->baseHotelsSubquery($currencyId, $validRule, [
            'category_id' => $f['category_id'] ?? null,
            'board_type_id' => $f['board_type_id'] ?? null,
            'location_ids' => null, // exclude self
            'guests' => $f['guests'] ?? 1,
            'checkin' => $f['checkin'] ?? null,
            'checkout' => $f['checkout'] ?? null,
        ]);

        $cityId     = $f['city_id'] ?? null;
        $districtId = $f['district_id'] ?? null;
        $areaId     = $f['area_id'] ?? null;

        $rows = \DB::table('hotels as h')
            ->join('locations as a', 'a.id', '=', 'h.location_id')       // area (leaf)
            ->leftJoin('locations as d', 'd.id', '=', 'a.parent_id')     // district
            ->leftJoin('locations as c', 'c.id', '=', 'd.parent_id')     // city
            ->whereIn('h.id', $baseHotels)
            ->where('h.is_active', true)
            ->selectRaw('c.id as city_id, c.name as city_name, d.id as district_id, d.name as district_name, a.id as area_id, a.name as area_name')
            ->get();

        // ---- cities (unique by id) ----
        $cities = $rows
            ->filter(fn ($r) => ! empty($r->city_id) && ! empty($r->city_name))
            ->groupBy('city_id')
            ->map(fn ($g) => (object) ['id' => (int) $g->first()->city_id, 'name' => $g->first()->city_name])
            ->values();

        if (! $cityId && $cities->count() === 1) {
            $cityId = (int) $cities->first()->id;
        }

        // ---- districts (unique by id), filtered by city if locked/selected ----
        $districtRows = $rows
            ->filter(fn ($r) => ! empty($r->district_id) && ! empty($r->district_name))
            ->when($cityId, fn ($col) => $col->where('city_id', (int) $cityId));

        $districts = $districtRows
            ->groupBy('district_id')
            ->map(fn ($g) => (object) ['id' => (int) $g->first()->district_id, 'name' => $g->first()->district_name])
            ->values();

        if (! $districtId && $districts->count() === 1) {
            $districtId = (int) $districts->first()->id;
        }

        // ---- areas (unique by id), filtered by district if locked/selected ----
        $areaRows = $rows
            ->filter(fn ($r) => ! empty($r->area_id) && ! empty($r->area_name))
            ->when($districtId, fn ($col) => $col->where('district_id', (int) $districtId));

        $areas = $areaRows
            ->groupBy('area_id')
            ->map(fn ($g) => (object) ['id' => (int) $g->first()->area_id, 'name' => $g->first()->area_name])
            ->values();

        // UI hide flags (single option => hide)
        $hideCity     = $cities->count() <= 1;
        $hideDistrict = $districts->count() <= 1;
        $hideArea     = $areas->count() <= 1;

        return [
            $cities,
            $districts,
            $areas,
            [
                'hide_city' => $hideCity,
                'hide_district' => $hideDistrict,
                'hide_area' => $hideArea,
            ],
            [
                'city_id' => $cityId ? (int) $cityId : null,
                'district_id' => $districtId ? (int) $districtId : null,
                'area_id' => $areaId ? (int) $areaId : null,
            ],
        ];
    }
}
