<?php

namespace App\Http\Controllers;

use App\Models\BedType;
use App\Models\BoardType;
use App\Models\Currency;
use App\Models\Hotel;
use App\Models\HotelCategory;
use App\Models\Location;
use App\Models\Room;
use App\Models\RoomRateRule;
use App\Models\StaticPage;
use App\Services\CampaignPlacementViewService;
use App\Services\RoomRateResolver;
use App\Support\Helpers\CurrencyHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HotelController extends Controller
{
    /**
     * i18n kolonlarını normalize eder.
     *
     * - Düz string ise doğrudan döner.
     * - Locale-keyed array ise aktif locale'e göre string döner.
     * - Özel durum: notes gibi [{value:"..."}] listelerini satır satır birleştirir.
     */
    private function localize($value)
    {
        if (! is_array($value)) {
            return $value;
        }

        $locale = App::getLocale();

        $v = $value[$locale] ?? null;

        if (is_string($v)) {
            return $v;
        }

        if (is_array($v)) {
            return collect($v)
                ->pluck('value')
                ->filter()
                ->implode("\n");
        }

        return null;
    }

    /**
     * Not alanını (jsonb) aktif dile göre string listesine çevirir.
     *
     * Beklenen format:
     *  notes = {
     *      "tr": [ {"value": "..."} , "..."],
     *      "en": [ ... ]
     *  }
     */
    private function localizeNotes($notes): array
    {
        if (! is_array($notes)) {
            return [];
        }

        $locale = App::getLocale();

        $list = $notes[$locale] ?? null;
        if (! is_array($list)) {
            return [];
        }

        return collect($list)
            ->map(function ($item) {
                if (is_array($item)) {
                    $v = $item['value'] ?? null;
                } else {
                    $v = $item;
                }

                return is_string($v) ? trim($v) : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Otel için feature group + facility listesini view'e uygun formata çevirir.
     */
    private function mapFeatureGroups(Hotel $hotel): array
    {
        $locale = App::getLocale();

        return $hotel->featureGroups()
            ->ordered()
            ->with('facilities')
            ->get()
            ->map(function ($fg) use ($locale) {
                $title = $fg->title;

                if (is_array($title)) {
                    $category = $title[$locale] ?? (reset($title) ?: null);
                } else {
                    $category = $title;
                }

                $items = $fg->facilities
                    ->map(function ($facility) {
                        return $facility->name_l;
                    })
                    ->filter()
                    ->values()
                    ->all();

                if ($category === null || empty($items)) {
                    return null;
                }

                return [
                    'category' => $category,
                    'items'    => $items,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Yakındaki yerler alanını aktif dile göre map eder.
     */
    private function mapNearby($nearby): array
    {
        if (! is_array($nearby)) {
            return [];
        }

        $locale = App::getLocale();
        $base   = config('app.locale', 'tr');

        return collect($nearby)
            ->map(function ($item) use ($locale, $base) {
                if (! is_array($item)) {
                    return null;
                }

                $labelRaw    = $item['label']    ?? null;
                $distanceRaw = $item['distance'] ?? null;

                $label = is_array($labelRaw)
                    ? ($labelRaw[$locale] ?? $labelRaw[$base] ?? reset($labelRaw))
                    : $labelRaw;

                $distance = is_array($distanceRaw)
                    ? ($distanceRaw[$locale] ?? $distanceRaw[$base] ?? reset($distanceRaw))
                    : $distanceRaw;

                if (! $label || ! $distance) {
                    return null;
                }

                return [
                    'icon'     => $item['icon'] ?? null,
                    'label'    => $label,
                    'distance' => $distance,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Odaya bağlı yatak tiplerini tek satırlık bir özet string'e çevirir.
     */
    private function mapBedSummary(Room $room): ?string
    {
        if ($room->beds->isEmpty()) {
            return null;
        }

        $parts = $room->beds
            ->map(function (BedType $bed) {
                $qty  = $bed->pivot->quantity ?? 1;
                $name = $bed->name_l;

                if (! $name) {
                    return null;
                }

                return $qty > 1 ? $qty . ' x ' . $name : $name;
            })
            ->filter()
            ->values()
            ->all();

        if (empty($parts)) {
            return null;
        }

        return implode(', ', $parts);
    }

    /**
     * "18.11.2025 - 22.11.2025" veya "18.11.2025" formatını parse eder.
     *
     * Dönüş:
     *   [Carbon $checkin, Carbon $checkout, int $nights] veya null
     */
    private function parseDateRange(?string $raw): ?array
    {
        if (! $raw) {
            return null;
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

            $nights = $start->diffInDays($end);
            if ($nights < 1) {
                $nights = 1;
                $end    = (clone $start)->addDay();
            }

            return [$start, $end, $nights];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Verilen oda + form context için fiyatı çözer.
     */
    private function resolveRoomPricing(Room $room, array $context): ?array
    {
        $currencyId = $this->resolveCurrencyId();
        if (! $currencyId) {
            return null;
        }

        /** @var RoomRateResolver $resolver */
        $resolver = app(RoomRateResolver::class);

        $boardTypeId = $context['board_type_id'] ?? null;
        $adults      = max(1, (int) ($context['adults'] ?? 1));
        $children    = max(0, (int) ($context['children'] ?? 0));
        $nights      = max(1, (int) ($context['nights'] ?? 1));

        $checkin  = $context['checkin'];
        $checkout = $context['checkout'];

        $rangeEnd = (clone $checkout)->subDay();

        $range = $resolver->resolveRange(
            $room,
            $checkin->format('Y-m-d'),
            $rangeEnd->format('Y-m-d'),
            $currencyId,
            $boardTypeId,
            $adults,
            $children
        );

        if ($range->isEmpty()) {
            return null;
        }

        if ($range->contains(fn ($d) => ($d['closed'] ?? false) === true)) {
            return null;
        }

        if ($range->contains(fn ($d) => ($d['ok'] ?? false) !== true)) {
            return null;
        }

        $first = $range->first();
        $total = $range->sum('total');

        $currencyCode = $this->resolveCurrencyCode();
        $priceMode    = $first['price_mode'] ?? null; // 'room' | 'person'

        if ($priceMode === 'room') {
            $roomPerNight = (float) ($first['unit_amount'] ?? 0);

            return [
                'mode'           => 'per_room',
                'nights'         => $nights,
                'total_amount'   => $total,
                'currency'       => $currencyCode,
                'room_per_night' => $roomPerNight,
                'adult_count'      => $adults,
                'child_count'      => $children,
                'adult_per_night'  => null,
                'child_per_night'  => null,
            ];
        }

        $unitAmount = (float) ($first['unit_amount'] ?? 0);
        $childPct   = $first['meta']['child_discount_percent'] ?? null;

        $adultPerNight = $unitAmount;
        $childPerNight = $unitAmount;

        if ($childPct !== null) {
            $pct           = max(0, min(100, (float) $childPct));
            $childPerNight = $unitAmount * (1 - $pct / 100);
        }

        return [
            'mode'            => 'per_person',
            'nights'          => $nights,
            'total_amount'    => $total,
            'currency'        => $currencyCode,
            'room_per_night'  => null,
            'adult_count'     => $adults,
            'child_count'     => $children,
            'adult_per_night' => $adultPerNight,
            'child_per_night' => $children > 0 ? $childPerNight : null,
        ];
    }

    /**
     * Otelin odalarını view'e uygun yapı + form context state'i ile map eder.
     */
    private function mapRooms(Hotel $hotel, ?array $context = null): array
    {
        return $hotel->rooms()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Room $room) use ($context) {
                $images = $room->getMedia('gallery')
                    ->map(function (Media $media) use ($room) {
                        $img = \App\Support\Helpers\ImageHelper::normalize($media);

                        if ($room->name_l) {
                            $img['alt'] = $room->name_l;
                        }

                        return $img;
                    })
                    ->values()
                    ->all();

                if (empty($images)) {
                    $placeholder = \App\Support\Helpers\ImageHelper::normalize(null);
                    $placeholder['alt'] = $room->name_l ?? 'Oda görseli';
                    $images = [$placeholder];
                }

                $capacity = [
                    'adults'   => $room->capacity_adults ?? 0,
                    'children' => $room->capacity_children ?? 0,
                ];
                $maxGuests = ($capacity['adults'] ?? 0) + ($capacity['children'] ?? 0);

                $bedSummary = $this->mapBedSummary($room);
                $viewName   = $room->viewType?->name_l;

                $facilities = $room->facilities
                    ->map(fn ($f) => $f->name_l)
                    ->filter()
                    ->values()
                    ->all();

                $data = [
                    'id'          => $room->id,
                    'name'        => $room->name_l,
                    'description' => $this->localize($room->description),
                    'images'      => $images,
                    'size'        => $room->size_m2,
                    'bed_type'    => $bedSummary,
                    'capacity'    => $capacity,
                    'view'        => $viewName,
                    'smoking'     => (bool) $room->smoking,
                    'facilities'  => $facilities,
                    'state'            => 'no_context',
                    'max_nights'       => null,
                    'pricing'          => null,
                    'capacity_message' => null,
                    'stay_message'     => null,
                ];

                if ($context === null) {
                    return $data;
                }

                $totalGuests = (int) ($context['total_guests'] ?? 0);

                if ($maxGuests > 0 && $totalGuests > $maxGuests) {
                    $data['state']            = 'over_capacity';
                    $data['capacity_message'] = "Bu odanın maksimum kapasitesi {$maxGuests} kişidir.";
                    return $data;
                }

                $pricing = $this->resolveRoomPricing($room, $context);

                if ($pricing === null) {
                    $data['state'] = 'unavailable';
                    return $data;
                }

                $data['state']   = 'priced';
                $data['pricing'] = $pricing;

                return $data;
            })
            ->values()
            ->all();
    }

    /**
     * Otel galeri görsellerini conversion'larla birlikte map eder.
     */
    private function mapHotelImage(Media $media, string $fallbackAlt): array
    {
        return [
            'thumb'   => $media->getUrl('thumb'),
            'thumb2x' => $media->getUrl('thumb2x'),
            'small'   => $media->getUrl('small'),
            'small2x' => $media->getUrl('small2x'),
            'large'   => $media->getUrl('large'),
            'large2x' => $media->getUrl('large2x'),
            'alt'     => $media->getCustomProperty('alt') ?? $fallbackAlt,
        ];
    }

    private function mapGallery(Hotel $hotel): array
    {
        $altBase    = $this->localize($hotel->name) ?? 'Otel görseli';
        $mediaItems = $hotel->getMedia('gallery');

        if ($mediaItems->isEmpty()) {
            $cover = $hotel->getFirstMedia('cover');

            return $cover
                ? [$this->mapHotelImage($cover, $altBase)]
                : [];
        }

        return $mediaItems
            ->map(fn (Media $media) => $this->mapHotelImage($media, $altBase))
            ->values()
            ->all();
    }

    /**
     * Lokasyon bilgisini (region / city) map eder.
     */
    private function mapLocation(?Location $location): array
    {
        if (! $location) {
            return [
                'region' => null,
                'city'   => null,
            ];
        }

        $city   = $location->parent?->name;
        $region = $location->parent?->parent?->name;

        return [
            'region' => $region ?: $city,
            'city'   => $city,
        ];
    }

    private function resolveCurrencyCode(): string
    {
        return strtoupper(CurrencyHelper::currentCode());
    }

    private function resolveCurrencyId(): ?int
    {
        $code = $this->resolveCurrencyCode();

        return Currency::query()
            ->where('code', $code)
            ->value('id');
    }

    /**
     * Otel detay sayfası.
     */
    public function show(Request $request, string $slug, CampaignPlacementViewService $campaignService)
    {
        $locale = App::getLocale();

        $hotel = Hotel::query()
            ->with([
                'location.parent.parent',
                'media',
                'featureGroups.facilities',
                'rooms.media',
                'rooms.viewType',
                'rooms.facilities',
                'rooms.beds',
                'starRating',
            ])
            ->where('is_active', true)
            ->where("slug->{$locale}", $slug)
            ->firstOrFail();

        $checkinStr = $request->query('checkin');

        $adults   = (int) $request->query('adults', 2);
        $children = (int) $request->query('children', 0);
        $boardId  = $request->query('board_type_id');

        $context = null;

        if ($checkinStr) {
            $range = $this->parseDateRange($checkinStr);

            if ($range !== null) {
                [$ci, $co, $nights] = $range;

                $context = [
                    'checkin'       => $ci,
                    'checkout'      => $co,
                    'nights'        => $nights,
                    'adults'        => $adults,
                    'children'      => $children,
                    'total_guests'  => $adults + $children,
                    'board_type_id' => $boardId ? (int) $boardId : null,
                ];
            }
        }

        $gallery   = $this->mapGallery($hotel);
        $location  = $this->mapLocation($hotel->location);
        $features  = $this->mapFeatureGroups($hotel);
        $notes     = $this->localizeNotes($hotel->notes);
        $stars     = (int) ($hotel->starRating?->rating_value ?? 0);
        $nearby    = $this->mapNearby($hotel->nearby);
        $latitude  = $hotel->latitude ? (float) $hotel->latitude : null;
        $longitude = $hotel->longitude ? (float) $hotel->longitude : null;
        $rooms     = $this->mapRooms($hotel, $context);

        $roomIds = $hotel->rooms
            ->pluck('id')
            ->filter()
            ->values()
            ->all();

        $boardTypes = [];
        if (! empty($roomIds)) {
            $usedBoardTypeIds = RoomRateRule::query()
                ->whereIn('room_id', $roomIds)
                ->where('is_active', true)
                ->pluck('board_type_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (! empty($usedBoardTypeIds)) {
                $boardTypes = BoardType::query()
                    ->whereIn('id', $usedBoardTypeIds)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->map(fn (BoardType $bt) => [
                        'id'   => $bt->id,
                        'name' => $bt->name_l,
                    ])
                    ->values()
                    ->all();
            }
        }

        $viewData = [
            'id'             => $hotel->id,
            'name'           => $this->localize($hotel->name),
            'slug'           => $hotel->slug[$locale] ?? null,
            'description'    => $this->localize($hotel->description),
            'promo_video_id' => $hotel->promo_video_id,
            'stars'          => $stars,
            'nearby'         => $nearby,
            'location'       => $location,
            'latitude'       => $latitude,
            'longitude'      => $longitude,
            'images'         => ! empty($gallery) ? $gallery : ['/images/default.jpg'],
            'rooms'          => $rooms,
            'features'       => $features,
            'notes'          => $notes,
            'board_types'    => $boardTypes,
        ];

        return view('pages.hotel.hotel-detail', [
            'hotel'        => $viewData,
            'searchParams' => $context,
            'campaigns'    => $campaignService->buildForPlacement('hotel_detail'),
        ]);
    }

    public function index(Request $request)
    {
        $locale = App::getLocale();

        $page = StaticPage::query()
            ->where('key', 'hotel_page')
            ->where('is_active', true)
            ->firstOrFail();

        $c   = $page->content ?? [];
        $loc = app()->getLocale();

        // ---- Filter inputs (Sprint-1 contract) ----
        $categoryId  = $request->query('category_id');
        $boardTypeId = $request->query('board_type_id');

        $cityId     = $request->query('city_id');
        $districtId = $request->query('district_id');
        $areaId     = $request->query('area_id');

        $guests = max(1, (int) $request->query('guests', 2));

        $checkinRaw = $request->query('checkin');

        $checkin  = null;
        $checkout = null;

        if ($checkinRaw) {
            $range = $this->parseDateRange($checkinRaw);
            if ($range !== null) {
                [$checkin, $checkout] = $range;
            }
        }

        // ---- Currency ----
        $currencyCode = CurrencyHelper::currentCode();
        $currencyId   = Currency::query()
            ->where('code', strtoupper($currencyCode))
            ->value('id');

        if (! $currencyId) {
            return view('pages.hotel.index', [
                'hotels' => collect(),
                'page' => $page,
                'c' => $c,
                'loc' => $loc,
                'currencyCode' => strtoupper($currencyCode),
                'categories' => collect(),
                'boardTypes' => collect(),
                'maxGuests' => 1,
                'cityOptions' => collect(),
                'districtOptions' => collect(),
                'areaOptions' => collect(),
                'filters' => [
                    'category_id' => $categoryId ? (int) $categoryId : null,
                    'board_type_id' => $boardTypeId ? (int) $boardTypeId : null,
                    'guests' => $guests,
                    'checkin' => $checkinRaw,
                    'city_id' => $cityId ? (int) $cityId : null,
                    'district_id' => $districtId ? (int) $districtId : null,
                    'area_id' => $areaId ? (int) $areaId : null,
                ],
            ]);
        }

        // ---- Base validity rule for listing/filters (AC-2 + AC-7) ----
        $validRule = function ($q) use ($currencyId) {
            $q->where('rrr.is_active', true)
                ->where('rrr.closed', false)
                ->where('rrr.currency_id', $currencyId)
                ->where(function ($qq) {
                    $qq->whereNull('rrr.allotment')
                        ->orWhere('rrr.allotment', '>', 0);
                });
        };

        // ---- Eligible hotel location ids (for location option datasets) ----
        $eligibleAreaLocationIds = Hotel::query()
            ->where('hotels.is_active', true)
            ->when($categoryId, fn ($q) => $q->where('hotels.hotel_category_id', (int) $categoryId))
            ->when($guests > 0, function ($q) use ($guests) {
                $q->whereExists(function ($qq) use ($guests) {
                    $qq->selectRaw('1')
                        ->from('rooms as rcap')
                        ->whereColumn('rcap.hotel_id', 'hotels.id')
                        ->where('rcap.is_active', true)
                        ->whereRaw('(COALESCE(rcap.capacity_adults,0) + COALESCE(rcap.capacity_children,0)) >= ?', [$guests]);
                });
            })
            ->when($boardTypeId, function ($q) use ($boardTypeId, $validRule) {
                $boardTypeId = (int) $boardTypeId;

                $q->whereExists(function ($qq) use ($boardTypeId, $validRule) {
                    $qq->selectRaw('1')
                        ->from('rooms as r')
                        ->join('room_rate_rules as rrr', 'rrr.room_id', '=', 'r.id')
                        ->whereColumn('r.hotel_id', 'hotels.id')
                        ->where('r.is_active', true)
                        ->where('rrr.board_type_id', $boardTypeId);

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
                        ->whereColumn('r.hotel_id', 'hotels.id')
                        ->where('r.is_active', true);

                    $validRule($qq);

                    $qq->where(function ($q0) use ($rangeStart, $rangeEnd) {
                        $q0->where(function ($a) {
                            $a->whereNull('rrr.date_start')
                                ->whereNull('rrr.date_end');
                        })->orWhere(function ($b) use ($rangeStart, $rangeEnd) {
                            $b->where(function ($x) use ($rangeEnd) {
                                $x->whereNull('rrr.date_start')
                                    ->orWhere('rrr.date_start', '<=', $rangeEnd);
                            })->where(function ($y) use ($rangeStart) {
                                $y->whereNull('rrr.date_end')
                                    ->orWhere('rrr.date_end', '>=', $rangeStart);
                            });
                        });
                    });
                });
            })
            ->select('hotels.location_id')
            ->distinct()
            ->pluck('location_id')
            ->filter()
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();

        $areasWithParents = Location::query()
            ->with('parent.parent')
            ->whereIn('id', $eligibleAreaLocationIds)
            ->get(['id', 'parent_id', 'name', 'type']);

        $availableDistrictIds = $areasWithParents
            ->map(fn ($a) => $a->parent?->id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $availableCityIds = $areasWithParents
            ->map(fn ($a) => $a->parent?->parent?->id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $cityOptions = Location::query()
            ->whereIn('id', $availableCityIds)
            ->where('type', 'province')
            ->orderBy('name')
            ->get(['id', 'name']);

        if (! $cityId && $cityOptions->count() === 1) {
            $cityId = (int) $cityOptions->first()->id;
        }

        $districtOptions = Location::query()
            ->whereIn('id', $availableDistrictIds)
            ->where('type', 'district')
            ->when($cityId, fn ($q) => $q->where('parent_id', (int) $cityId))
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name']);

        if (! $districtId && $districtOptions->count() === 1) {
            $districtId = (int) $districtOptions->first()->id;
        }

        $areaOptions = Location::query()
            ->whereIn('id', $eligibleAreaLocationIds)
            ->where('type', 'area')
            ->when($districtId, fn ($q) => $q->where('parent_id', (int) $districtId))
            ->orderBy('name')
            ->get(['id', 'parent_id', 'name']);

        if (! $areaId && $areaOptions->count() === 1) {
            $areaId = (int) $areaOptions->first()->id;
        }

        // priority: area > district > city
        $selectedLocationId = $areaId ?: ($districtId ?: $cityId);

        // ---- Location subtree ids (adjacency) ----
        $locationIds = null;

        if ($selectedLocationId) {
            $selectedLocationId = (int) $selectedLocationId;

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
                [$selectedLocationId]
            );

            $locationIds = collect($rows)->pluck('id')->map(fn ($v) => (int) $v)->values()->all();

            if (empty($locationIds)) {
                $locationIds = [$selectedLocationId];
            }
        }

        // ---- Subquery: min price + price_type per hotel (unchanged) ----
        $minPriceSub = \DB::table('room_rate_rules as rrr')
            ->join('rooms as r', 'r.id', '=', 'rrr.room_id')
            ->selectRaw('DISTINCT ON (r.hotel_id) r.hotel_id, rrr.amount as from_price_amount, rrr.price_type as from_price_type')
            ->where('r.is_active', true)
            ->where(function ($q) use ($validRule) {
                $validRule($q);
            })
            ->orderBy('r.hotel_id')
            ->orderBy('rrr.amount', 'asc')
            ->orderBy('rrr.id', 'asc');

        // ---- Hotels query ----
        $hotelsQuery = Hotel::query()
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
            ]);

        // F-1: Category
        if ($categoryId) {
            $hotelsQuery->where('hotels.hotel_category_id', (int) $categoryId);
        }

        // F-3: Location (selected + descendants)
        if (is_array($locationIds)) {
            $hotelsQuery->whereIn('hotels.location_id', $locationIds);
        }

        // F-5: Capacity total
        if ($guests > 0) {
            $hotelsQuery->whereExists(function ($q) use ($guests) {
                $q->selectRaw('1')
                    ->from('rooms as rcap')
                    ->whereColumn('rcap.hotel_id', 'hotels.id')
                    ->where('rcap.is_active', true)
                    ->whereRaw('(COALESCE(rcap.capacity_adults,0) + COALESCE(rcap.capacity_children,0)) >= ?', [$guests]);
            });
        }

        // F-4: Board type
        if ($boardTypeId) {
            $boardTypeId = (int) $boardTypeId;

            $hotelsQuery->whereExists(function ($q) use ($boardTypeId, $validRule) {
                $q->selectRaw('1')
                    ->from('rooms as r')
                    ->join('room_rate_rules as rrr', 'rrr.room_id', '=', 'r.id')
                    ->whereColumn('r.hotel_id', 'hotels.id')
                    ->where('r.is_active', true)
                    ->where('rrr.board_type_id', $boardTypeId);

                $validRule($q);
            });
        }

        // F-2: Date range (only narrow list)
        if ($checkin && $checkout) {
            $rangeStart = $checkin->toDateString();
            $rangeEnd   = (clone $checkout)->subDay()->toDateString();

            $hotelsQuery->whereExists(function ($q) use ($rangeStart, $rangeEnd, $validRule) {
                $q->selectRaw('1')
                    ->from('rooms as r')
                    ->join('room_rate_rules as rrr', 'rrr.room_id', '=', 'r.id')
                    ->whereColumn('r.hotel_id', 'hotels.id')
                    ->where('r.is_active', true);

                $validRule($q);

                $q->where(function ($qq) use ($rangeStart, $rangeEnd) {
                    $qq->where(function ($q0) {
                        $q0->whereNull('rrr.date_start')
                            ->whereNull('rrr.date_end');
                    })->orWhere(function ($q1) use ($rangeStart, $rangeEnd) {
                        $q1->where(function ($a) use ($rangeEnd) {
                            $a->whereNull('rrr.date_start')
                                ->orWhere('rrr.date_start', '<=', $rangeEnd);
                        })->where(function ($b) use ($rangeStart) {
                            $b->whereNull('rrr.date_end')
                                ->orWhere('rrr.date_end', '>=', $rangeStart);
                        });
                    });
                });
            });
        }

        // UI için max kapasite (1..max) — guests filtresi hariç
        $maxGuests = (int) \DB::table('rooms as r')
            ->join('hotels as h', 'h.id', '=', 'r.hotel_id')
            ->where('h.is_active', true)
            ->where('r.is_active', true)
            ->when($categoryId, fn ($q) => $q->where('h.hotel_category_id', (int) $categoryId))
            ->when(is_array($locationIds), fn ($q) => $q->whereIn('h.location_id', $locationIds))
            ->whereExists(function ($q) use ($currencyId, $boardTypeId, $checkin, $checkout, $validRule) {
                $q->selectRaw('1')
                    ->from('room_rate_rules as rrr')
                    ->whereColumn('rrr.room_id', 'r.id');

                $validRule($q);

                if ($boardTypeId) {
                    $q->where('rrr.board_type_id', (int) $boardTypeId);
                }

                if ($checkin && $checkout) {
                    $rangeStart = $checkin->toDateString();
                    $rangeEnd   = (clone $checkout)->subDay()->toDateString();

                    $q->where(function ($qq) use ($rangeStart, $rangeEnd) {
                        $qq->where(function ($q0) {
                            $q0->whereNull('rrr.date_start')
                                ->whereNull('rrr.date_end');
                        })->orWhere(function ($q1) use ($rangeStart, $rangeEnd) {
                            $q1->where(function ($a) use ($rangeEnd) {
                                $a->whereNull('rrr.date_start')
                                    ->orWhere('rrr.date_start', '<=', $rangeEnd);
                            })->where(function ($b) use ($rangeStart) {
                                $b->whereNull('rrr.date_end')
                                    ->orWhere('rrr.date_end', '>=', $rangeStart);
                            });
                        });
                    });
                }
            })
            ->max(\DB::raw('(COALESCE(r.capacity_adults,0) + COALESCE(r.capacity_children,0))'));

        $maxGuests = max(1, $maxGuests);

        $hotels = $hotelsQuery
            ->orderByRaw("hotels.name->>? asc", [$locale])
            ->get();

        // ---- Option datasets for other filter UI ----
        $categories = HotelCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        // ---- BoardType options: sadece kayıt varsa (mevcut filtre bağlamında) ----
// Not: board type options üretirken, mevcut board_type_id filtresini bilerek KULLANMIYORUZ
// (yoksa select kendini kilitler).
        $availableBoardTypeIdsQuery = \DB::table('room_rate_rules as rrr')
            ->join('rooms as r', 'r.id', '=', 'rrr.room_id')
            ->join('hotels as h', 'h.id', '=', 'r.hotel_id')
            ->where('h.is_active', true)
            ->where('r.is_active', true)
            ->whereNotNull('rrr.board_type_id');

// geçerli kural (AC-2 + AC-7)
        $validRule($availableBoardTypeIdsQuery);

// category
        if ($categoryId) {
            $availableBoardTypeIdsQuery->where('h.hotel_category_id', (int) $categoryId);
        }

// location (city/district/area seçimine göre ürettiğin $locationIds varsa)
        if (is_array($locationIds)) {
            $availableBoardTypeIdsQuery->whereIn('h.location_id', $locationIds);
        }

// guests
        if ($guests > 0) {
            $availableBoardTypeIdsQuery->whereRaw('(COALESCE(r.capacity_adults,0) + COALESCE(r.capacity_children,0)) >= ?', [$guests]);
        }

// date range
        if ($checkin && $checkout) {
            $rangeStart = $checkin->toDateString();
            $rangeEnd   = (clone $checkout)->subDay()->toDateString();

            $availableBoardTypeIdsQuery->where(function ($qq) use ($rangeStart, $rangeEnd) {
                $qq->where(function ($q0) {
                    $q0->whereNull('rrr.date_start')
                        ->whereNull('rrr.date_end');
                })->orWhere(function ($q1) use ($rangeStart, $rangeEnd) {
                    $q1->where(function ($a) use ($rangeEnd) {
                        $a->whereNull('rrr.date_start')
                            ->orWhere('rrr.date_start', '<=', $rangeEnd);
                    })->where(function ($b) use ($rangeStart) {
                        $b->whereNull('rrr.date_end')
                            ->orWhere('rrr.date_end', '>=', $rangeStart);
                    });
                });
            });
        }

        $availableBoardTypeIds = $availableBoardTypeIdsQuery
            ->distinct()
            ->pluck('rrr.board_type_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $boardTypes = empty($availableBoardTypeIds)
            ? collect()
            : BoardType::query()
                ->where('is_active', true)
                ->whereIn('id', $availableBoardTypeIds)
                ->orderBy('sort_order')
                ->get(['id', 'name']);


        return view('pages.hotel.index', [
            'hotels' => $hotels,
            'page' => $page,
            'c' => $c,
            'loc' => $loc,
            'currencyCode' => strtoupper($currencyCode),

            'categories' => $categories,
            'boardTypes' => $boardTypes,
            'maxGuests' => $maxGuests,

            // NEW: location UI options
            'cityOptions' => $cityOptions,
            'districtOptions' => $districtOptions,
            'areaOptions' => $areaOptions,

            'filters' => [
                'category_id' => $categoryId ? (int) $categoryId : null,
                'board_type_id' => $boardTypeId ? (int) $boardTypeId : null,
                'guests' => $guests,
                'checkin' => $checkinRaw,

                'city_id' => $cityId ? (int) $cityId : null,
                'district_id' => $districtId ? (int) $districtId : null,
                'area_id' => $areaId ? (int) $areaId : null,
            ],
        ]);
    }
}
