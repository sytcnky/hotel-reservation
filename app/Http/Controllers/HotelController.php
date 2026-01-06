<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\BedType;
use App\Models\Hotel;
use App\Models\Location;
use App\Models\Room;
use App\Models\BoardType;
use App\Models\RoomRateRule;
use App\Models\Currency;
use App\Services\RoomRateResolver;
use App\Support\Helpers\CurrencyHelper;
use App\Models\StaticPage;

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
            // string veya null ise direkt döndür
            return $value;
        }

        $locale = App::getLocale();

        $v = $value[$locale] ?? null;

        // string ise direkt döndür
        if (is_string($v)) {
            return $v;
        }

        // ARRAY ise (ör: notes: [{value:"..."}]) → satır satır birleştir
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
     *
     * [
     *   [
     *     'category' => 'Genel Özellikler',
     *     'items'    => ['Açık havuz', 'Kapalı havuz', ...],
     *   ],
     *   ...
     * ]
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
     *
     * Kaynak format (örnek):
     *  nearby = [
     *    {
     *      "icon": "fi-rr-shop",
     *      "label": { "tr": "Market", "en": "Market" },
     *      "distance": { "tr": "2 dk. sürüş", "en": "2 min drive" }
     *    },
     *    ...
     *  ]
     *
     * View format:
     *  [
     *    ['icon' => '...', 'label' => 'Market', 'distance' => '2 dk. sürüş'],
     *    ...
     *  ]
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

                $label =
                    is_array($labelRaw)
                        ? ($labelRaw[$locale] ?? $labelRaw[$base] ?? reset($labelRaw))
                        : $labelRaw;

                $distance =
                    is_array($distanceRaw)
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
     *
     * Örnek:
     *   "1 x Çift Kişilik Yatak, 1 x Tek Kişilik Yatak"
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

                return $qty > 1
                    ? $qty . ' x ' . $name
                    : $name;
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
     *
     * Not:
     *  - Tek tarih verilirse checkout = checkin+1, nights = 1
     *  - Bitiş <= başlangıç ise yine checkout = checkin+1, nights = 1
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
     * Dönüş:
     * - null       => müsait değil / kural bulunamadı / kapalı
     * - array      => Blade'de kullanılan pricing yapısı
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

        // Geceleme mantığı: [checkin .. checkout-1] geceleri fiyatlanır.
        // RoomRateResolver::resolveRange, kendisine verilen end tarihini "son gece günü"
        // gibi kabul ettiği için biz checkout-1 gününü gönderiyoruz.
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

        // Kapalı veya kural bulunamayan gün varsa odanın tamamını "unavailable" sayalım
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
            // Oda bazlı (room per night)
            $roomPerNight = (float) ($first['unit_amount'] ?? 0);

            return [
                'mode'           => 'per_room',
                'nights'         => $nights,
                'total_amount'   => $total,
                'currency'       => $currencyCode,
                'room_per_night' => $roomPerNight,

                // Kişi detayları bu modda gösterilmeyecek
                'adult_count'      => $adults,
                'child_count'      => $children,
                'adult_per_night'  => null,
                'child_per_night'  => null,
            ];
        }

        // Kişi bazlı (person per night)
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

            'room_per_night'  => null, // kişi bazlı olduğu için
            'adult_count'     => $adults,
            'child_count'     => $children,
            'adult_per_night' => $adultPerNight,
            'child_per_night' => $children > 0 ? $childPerNight : null,
        ];
    }

    /**
     * Otelin odalarını view'e uygun yapı + form context state'i ile map eder.
     *
     * Her oda için temel alanlar:
     *  - id, name, description, images, size, bed_type, capacity, view, smoking, facilities
     *
     * State alanları:
     *  - state: no_context | over_capacity | over_max_nights | unavailable | priced
     *  - max_nights: (şimdilik null, ileride rate rule / oda alanından beslenecek)
     *  - pricing: RoomRateResolver çıktısı
     *  - capacity_message, stay_message: uyarı metinleri
     */
    private function mapRooms(Hotel $hotel, ?array $context = null): array
    {
        return $hotel->rooms()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Room $room) use ($context) {
                // Görseller (Media Library + normalize)
                $images = $room->getMedia('gallery')
                    ->map(function (Media $media) use ($room) {
                        $img = \App\Support\Helpers\ImageHelper::normalize($media);

                        // Alt metni oda adıyla override edelim (varsa)
                        if ($room->name_l) {
                            $img['alt'] = $room->name_l;
                        }

                        return $img;
                    })
                    ->values()
                    ->all();

                if (empty($images)) {
                    // Hiç görsel yoksa tek bir placeholder üret
                    $placeholder = \App\Support\Helpers\ImageHelper::normalize(null);
                    $placeholder['alt'] = $room->name_l ?? 'Oda görseli';

                    $images = [$placeholder];
                }

                // Kapasite
                $capacity = [
                    'adults'   => $room->capacity_adults ?? 0,
                    'children' => $room->capacity_children ?? 0,
                ];
                $maxGuests = ($capacity['adults'] ?? 0) + ($capacity['children'] ?? 0);

                // Yatak özeti
                $bedSummary = $this->mapBedSummary($room);

                // Manzara
                $viewName = $room->viewType?->name_l;

                // Oda özellik rozetleri
                $facilities = $room->facilities
                    ->map(fn ($f) => $f->name_l)
                    ->filter()
                    ->values()
                    ->all();

                // Baz yapı + state placeholder'ları
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

                    // State alanları
                    'state'            => 'no_context',
                    'max_nights'       => null,
                    'pricing'          => null,
                    'capacity_message' => null,
                    'stay_message'     => null,
                ];

                // Context yoksa → sadece baz veriyi döndür
                if ($context === null) {
                    return $data;
                }

                // Toplam kişi
                $totalGuests = (int) ($context['total_guests'] ?? 0);

                // 1) Kapasite kontrolü
                if ($maxGuests > 0 && $totalGuests > $maxGuests) {
                    $data['state']            = 'over_capacity';
                    $data['capacity_message'] = "Bu odanın maksimum kapasitesi {$maxGuests} kişidir.";

                    return $data;
                }

                // 2) Maksimum gece kontrolü (şimdilik sadece kanca)
                // Örneğin ileride:
                // $data['max_nights'] = $room->max_nights ?? null;
                // if (! is_null($data['max_nights']) && $context['nights'] > $data['max_nights']) {
                //     $data['state']        = 'over_max_nights';
                //     $data['stay_message'] = "Bu odanın maksimum konaklama süresi {$data['max_nights']} gecedir.";
                //     return $data;
                // }

                // 3) Fiyatlandırma (RoomRateResolver entegrasyonu)
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
     *
     * Beklenen hiyerarşi:
     *  - area (otel)
     *  - city (parent)
     *  - region (parent.parent)
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

    /**
     * Aktif para biriminin kodunu döndürür (ör: "TRY").
     */
    private function resolveCurrencyCode(): string
    {
        return strtoupper(CurrencyHelper::currentCode());
    }

    /**
     * Aktif para biriminin ID'sini döndürür.
     */
    private function resolveCurrencyId(): ?int
    {
        $code = $this->resolveCurrencyCode();

        return Currency::query()
            ->where('code', $code)
            ->value('id');
    }

    /**
     * Otel detay sayfası.
     *
     * - URL: /{locale}/hotel/{slug}
     * - Slug: her dilde ayrı olabileceği için aktif locale'e göre sorgulanır.
     * - Form context:
     *    - checkin (d.m.Y veya "d.m.Y - d.m.Y" formatında)
     *    - adults, children
     *    - board_type_id
     */
    public function show(Request $request, string $slug)
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

        // Form context (tarih aralığı + kişi + board type)
        $checkinStr = $request->query('checkin');   // "gg.aa.yyyy - gg.aa.yyyy"

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

        /**
         * Bu otele ait odalarda kullanılan board_type_id'leri çıkar.
         * Sadece aktif RoomRateRule kayıtlarına bakar, ilgili BoardType'ları getirir.
         */
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
                ->filter()      // null olanları at
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
        ]);
    }


    public function index(Request $request)
    {
        $locale = App::getLocale();

        $page = StaticPage::query()
            ->where('key', 'hotel_page')
            ->where('is_active', true)
            ->firstOrFail();

        $c = $page->content ?? [];
        $loc = app()->getLocale();

        $hotels = Hotel::query()
            ->with([
                'location.parent.parent',
                'featureGroups.facilities',
                'starRating',
                'media',
            ])
            ->where('is_active', true)
            ->orderBy("name->{$locale}")
            ->get();

        return view('pages.hotel.index', [
            'hotels' => $hotels,
            'page' => $page,
            'c' => $c,
            'loc' => $loc,
        ]);
    }

}
