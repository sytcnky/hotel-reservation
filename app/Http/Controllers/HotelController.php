<?php

namespace App\Http\Controllers;

use App\Models\BedType;
use App\Models\BoardType;
use App\Models\Currency;
use App\Models\Hotel;
use App\Models\Location;
use App\Models\Room;
use App\Models\RoomRateRule;
use App\Services\CampaignPlacementViewService;
use App\Services\HotelListingPageService;
use App\Services\RoomRateResolver;
use App\Support\Currency\CurrencyContext;
use App\Support\Helpers\I18nHelper;
use App\Support\Helpers\LocaleHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class HotelController extends Controller
{
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
        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();

        return I18nHelper::stringList($notes, $uiLocale, $baseLocale);
    }

    /**
     * Otel için feature group + facility listesini view'e uygun formata çevirir.
     */
    private function mapFeatureGroups(Hotel $hotel): array
    {
        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();

        return $hotel->featureGroups()
            ->ordered()
            ->with('facilities')
            ->get()
            ->map(function ($fg) use ($uiLocale, $baseLocale) {
                $category = I18nHelper::scalar($fg->title, $uiLocale, $baseLocale);

                $items = $fg->facilities
                    ->map(fn ($facility) => $facility->name_l)
                    ->filter()
                    ->values()
                    ->all();

                if (! $category || empty($items)) {
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

        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();

        return collect($nearby)
            ->map(function ($item) use ($uiLocale, $baseLocale) {
                if (! is_array($item)) {
                    return null;
                }

                $label = I18nHelper::scalar($item['label'] ?? null, $uiLocale, $baseLocale);
                $distance = I18nHelper::scalar($item['distance'] ?? null, $uiLocale, $baseLocale);

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
     *
     * Kararlar:
     * - Oda kartında üstte: total (çocuk indirimi dahil olabilir)
     * - Altta küçük: nightly baz fiyat (çocuk indirimi uygulanmadan)
     * - price_mode=room ise nightly = room_per_night
     * - price_mode=person ise nightly = adult_per_night (baz), child_per_night opsiyonel taşınabilir
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

        // checkout (çıkış) fiyatlanmaz; range inclusive çalıştığı için checkout-1
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

        // Kapalı / ok değilse fiyat çıkmaz
        if ($range->contains(fn ($d) => ($d['closed'] ?? false) === true)) {
            return null;
        }
        if ($range->contains(fn ($d) => ($d['ok'] ?? false) !== true)) {
            return null;
        }

        $first = $range->first();
        $total = (float) $range->sum('total');

        $currencyCode = $this->resolveCurrencyCode();
        if (! $currencyCode) {
            return null;
        }

        $priceMode  = $first['price_mode'] ?? null; // 'room' | 'person'
        $unitAmount = (float) ($first['unit_amount'] ?? 0); // baz nightly (adult baz / room baz)

        // Baz nightly her zaman unit_amount (çocuk indirimsiz baz)
        if ($priceMode === 'room') {
            return [
                'mode'             => 'per_room',
                'nights'           => $nights,
                'total_amount'     => $total,
                'currency'         => $currencyCode,

                // UI küçük satır: gecelik baz oda fiyatı
                'room_per_night'   => $unitAmount,

                // bilgi amaçlı
                'adult_count'      => $adults,
                'child_count'      => $children,
                'adult_per_night'  => null,
                'child_per_night'  => null,
            ];
        }

        // person mode: nightly küçük satırda yetişkin baz fiyatı gösterilecek
        $adultPerNight = $unitAmount;

        // Çocuk indirimi sadece bilgi/opsiyonel; UI isterse ayrı satır gösterebilir.
        $childPerNight = null;
        if ($children > 0) {
            $childPerNight = $unitAmount;

            $childPct = $first['meta']['child_discount_percent'] ?? null;
            if ($childPct !== null) {
                $pct = max(0, min(100, (float) $childPct));
                $childPerNight = $unitAmount * (1 - $pct / 100);
            }
        }

        return [
            'mode'            => 'per_person',
            'nights'          => $nights,
            'total_amount'    => $total,
            'currency'        => $currencyCode,

            // UI küçük satır (baz): yetişkin gecelik (indirimsiz baz)
            'room_per_night'  => null,
            'adult_count'     => $adults,
            'child_count'     => $children,
            'adult_per_night' => $adultPerNight,

            // bilgi/opsiyonel
            'child_per_night' => $childPerNight,
        ];
    }

    /**
     * Otelin odalarını view'e uygun yapı + form context state'i ile map eder.
     *
     * Not: show() içinde 'rooms', 'rooms.media', 'rooms.viewType', 'rooms.facilities', 'rooms.beds'
     * eager-load edildiği için burada tekrar query atmayız; $hotel->rooms collection'ı üzerinden ilerleriz.
     */
    private function mapRooms(Hotel $hotel, ?array $context = null): array
    {
        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();

        $rooms = $hotel->rooms
            ->where('is_active', true)
            ->sortBy('sort_order')
            ->values();

        return $rooms
            ->map(function (Room $room) use ($context, $uiLocale, $baseLocale) {
                // Görseller: standart accessor (Room::gallery_images)
                // Controller içinde alt/placeholder override yok.
                $images = $room->gallery_images;

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
                    'id'               => $room->id,
                    'name'             => $room->name_l,
                    'description'      => I18nHelper::scalar($room->description, $uiLocale, $baseLocale),
                    'images'           => $images,
                    'size'             => $room->size_m2,
                    'bed_type'         => $bedSummary,
                    'capacity'         => $capacity,
                    'view'             => $viewName,
                    'smoking'          => (bool) $room->smoking,
                    'facilities'       => $facilities,
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

    private function resolveCurrencyCode(): ?string
    {
        $code = CurrencyContext::code(request());

        $code = strtoupper(trim((string) $code));
        return $code !== '' ? $code : null;
    }

    private function resolveCurrencyId(): ?int
    {
        $code = $this->resolveCurrencyCode();
        if (! $code) {
            return null;
        }

        return Currency::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->value('id');
    }

    /**
     * Otel detay sayfası.
     */
    public function show(Request $request, string $slug, CampaignPlacementViewService $campaignService)
    {
        $locale = App::getLocale();

        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();

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

        $gallery   = $hotel->gallery_images;
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
            'name'           => I18nHelper::scalar($hotel->name, $uiLocale, $baseLocale),
            'slug'           => $hotel->slug[$locale] ?? null,
            'description'    => I18nHelper::scalar($hotel->description, $uiLocale, $baseLocale),
            'promo_video_id' => $hotel->promo_video_id,
            'stars'          => $stars,
            'nearby'         => $nearby,
            'location'       => $location,
            'latitude'       => $latitude,
            'longitude'      => $longitude,
            'hotel_gallery'  => $gallery,
            'cover'          => $hotel->cover_image,
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

    /**
     * Otel listeleme sayfası: tek kaynak HotelListingPageService.
     */
    public function index(Request $request, HotelListingPageService $service)
    {
        $data = $service->build($request);

        return view('pages.hotel.index', $data);
    }
}
