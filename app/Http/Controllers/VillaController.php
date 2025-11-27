<?php

namespace App\Http\Controllers;

use App\Models\Villa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Support\Helpers\ImageHelper;

class VillaController extends Controller
{
    /**
     * Basit i18n helper:
     * - Düz string ise direkt döner
     * - Locale-keyed array ise aktif dil / base dil / ilk eleman sırasıyla döner
     */
    private function localizeScalar(mixed $value, string $locale, string $base): ?string
    {
        if (! is_array($value)) {
            return $value !== null ? (string) $value : null;
        }

        $v = $value[$locale] ?? $value[$base] ?? reset($value);

        return is_string($v) ? $v : (is_scalar($v) ? (string) $v : null);
    }

    /**
     * Repeater tarzı alanları (ör: highlights, stay_info) aktif dilde düz listeye çevirir.
     *
     * Beklenen olası formatlar:
     *  [
     *    "tr" => [ {"value": "..."} , {"value": "..."} ],
     *    "en" => [ ... ]
     *  ]
     *  veya
     *  [
     *    "tr" => [ "..." , "..." ],
     *    "en" => [ ... ]
     *  ]
     */
    private function localizeList(mixed $value, string $locale, string $base): array
    {
        if (! is_array($value)) {
            return [];
        }

        $list = $value[$locale] ?? $value[$base] ?? [];

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
     * Media'yi x-responsive-image için normalize eder.
     */
    private function mapImage(?Media $media, string $fallbackAlt = ''): array
    {
        $img = ImageHelper::normalize($media);

        if ($fallbackAlt && empty($img['alt'])) {
            $img['alt'] = $fallbackAlt;
        }

        return $img;
    }

    /**
     * Villa listeleme
     */
    public function index(Request $request)
    {
        $locale        = app()->getLocale();
        $baseLang      = config('app.locale', 'tr');
        $currencyCode  = \App\Support\Helpers\CurrencyHelper::currentCode();

        $villas = Villa::query()
            ->with(['location.parent.parent', 'category', 'media', 'rateRules.currency'])
            ->where('is_active', true)
            ->orderBy("name->{$locale}")
            ->get()
            ->map(function (Villa $villa) use ($locale, $baseLang, $currencyCode) {

                // İsim (jsonb)
                $nameSource = $villa->name;
                if (is_array($nameSource)) {
                    $name = $nameSource[$locale] ?? ($nameSource[$baseLang] ?? reset($nameSource));
                } else {
                    $name = $nameSource;
                }

                // Slug (jsonb)
                $slugSource = $villa->slug;
                if (is_array($slugSource)) {
                    $slug = $slugSource[$locale] ?? ($slugSource[$baseLang] ?? reset($slugSource));
                } else {
                    $slug = $slugSource;
                }

                // Kapak görseli → normalize (placeholder dahil)
                $coverMedia = $villa->getFirstMedia('cover');
                $cover      = ImageHelper::normalize($coverMedia);

                // Lokasyon
                $area   = $villa->location;
                $city   = $area?->parent?->name;
                $region = $area?->parent?->parent?->name;

                // Fiyat (liste için de aynı helper)
                $price = $villa->getBasePrice($currencyCode);

                return [
                    'id'             => $villa->id,
                    'slug'           => $slug,
                    'name'           => $name ?? 'Villa',    // listelemede direkt string kullanalım
                    'max_guests'     => $villa->max_guests,
                    'bedroom_count'  => $villa->bedroom_count,
                    'bathroom_count' => $villa->bathroom_count,
                    'location'       => [
                        'city'   => $city,
                        'region' => $region,
                    ],
                    'category_name'  => $villa->category?->name_l,
                    'cover'          => $cover,           // x-responsive-image uyumlu
                    'price'          => $price,
                    'currency'       => $currencyCode,
                ];
            })
            ->values()
            ->all();

        return view('pages.villa.index', compact('villas'));
    }

    /**
     * Villa detay
     *
     * Blade'deki beklenen yapı:
     * - $villa['name']                string
     * - $villa['max_guests']          int|null
     * - $villa['bedroom_count']       int|null
     * - $villa['bathroom_count']      int|null
     * - $villa['location']['city']    string|null
     * - $villa['location']['region']  string|null
     * - $villa['gallery']             array<image-array> (x-responsive-image formatında)
     * - $villa['base_price']          float|null
     * - $villa['currency']            string
     * - $villa['description']         string|null
     * - $villa['highlights']          array<string>
     * - $villa['accommodation_info']  array<string>
     * - $villa['nearby_places']       array<['icon','label','value']>
     * - $villa['promo_video_id']      string|null
     * - $villa['latitude'], ['longitude']
     */
    public function show(string $slug)
    {
        $locale   = App::getLocale();
        $baseLang = config('app.locale', 'tr');

        $villa = Villa::query()
            ->with([
                'location.parent.parent',
                'category',
                'rateRules.currency',
                'media',
                'featureGroups.amenities',
            ])
            ->where('canonical_slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $location = $villa->location;

        // Şehir / bölge
        $city   = $location?->parent?->name ?? $location?->name;
        $region = $location?->parent?->parent?->name;

        // İsim / açıklama i18n
        $name        = $this->localizeScalar($villa->name, $locale, $baseLang) ?? 'Villa';
        $description = $this->localizeScalar($villa->description, $locale, $baseLang);

        // Kategori → badge
        $categoryLabel = null;
        if ($villa->category) {
            $categoryLabel = $this->localizeScalar($villa->category->name, $locale, $baseLang);
        }

        // === FİYAT + MIN/MAX NIGHTS ===
        $selectedCurrency = \App\Support\Helpers\CurrencyHelper::currentCode();

        $rule = $villa->rateRules()
            ->whereHas('currency', fn ($q) => $q->where('code', $selectedCurrency))
            ->where('is_active', true)
            ->where('closed', false)
            ->orderBy('priority', 'asc')
            ->orderBy('date_start', 'asc')
            ->first();

        $basePrice = null;
        $currency  = $selectedCurrency;
        $minNights = null;
        $maxNights = null;

        if ($rule && (float) $rule->amount > 0) {
            $basePrice = (float) $rule->amount;
            $currency  = $rule->currency?->code ?? $selectedCurrency;
            $minNights = $rule->min_nights ?: null;
            $maxNights = $rule->max_nights ?: null;
        }

        // Galeri (Media Library → normalize)
        $galleryMedia = $villa->getMedia('gallery');

        if ($galleryMedia->isEmpty()) {
            $coverMedia = $villa->getFirstMedia('cover');
            if ($coverMedia) {
                $gallery = [$this->mapImage($coverMedia, $name)];
            } else {
                // Global placeholder
                $gallery = [ImageHelper::normalize(null)];
            }
        } else {
            $gallery = $galleryMedia
                ->map(fn (Media $media) => $this->mapImage($media, $name))
                ->values()
                ->all();
        }

        // Yakındaki yerler
        $nearbyPlaces = [];
        foreach ((array) $villa->nearby as $item) {
            if (! is_array($item)) {
                continue;
            }

            $labelSrc    = $item['label']    ?? null;
            $distanceSrc = $item['distance'] ?? null;

            $label =
                is_array($labelSrc)
                    ? ($labelSrc[$locale] ?? $labelSrc[$baseLang] ?? reset($labelSrc))
                    : $labelSrc;

            $distance =
                is_array($distanceSrc)
                    ? ($distanceSrc[$locale] ?? $distanceSrc[$baseLang] ?? reset($distanceSrc))
                    : $distanceSrc;

            if (! $label && ! $distance) {
                continue;
            }

            $nearbyPlaces[] = [
                'icon'  => $item['icon'] ?? 'fi-rr-marker',
                'label' => $label,
                'value' => $distance,
            ];
        }

        // Öne çıkanlar / konaklama bilgisi aktif dilde düz liste
        $highlights        = $this->localizeList($villa->highlights, $locale, $baseLang);
        $accommodationInfo = $this->localizeList($villa->stay_info, $locale, $baseLang);

        $viewData = [
            'id'             => $villa->id,
            'slug'           => $villa->canonical_slug,
            'name'           => $name,

            'max_guests'     => $villa->max_guests,
            'bedroom_count'  => $villa->bedroom_count,
            'bathroom_count' => $villa->bathroom_count,

            'location' => [
                'city'   => $city,
                'region' => $region,
            ],

            'gallery'         => $gallery,
            'base_price'      => $basePrice,
            'currency'        => $currency,
            'prepayment_rate' => (float) $villa->prepayment_rate,
            'min_nights'      => $minNights,
            'max_nights'      => $maxNights,

            'description'        => $description,
            'category_name'      => $categoryLabel,
            'highlights'         => $highlights,
            'accommodation_info' => $accommodationInfo,

            'promo_video_id'  => $villa->promo_video_id,
            'nearby_places'   => $nearbyPlaces,
            'latitude'        => $villa->latitude,
            'longitude'       => $villa->longitude,
        ];

        return view('pages.villa.villa-detail', [
            'villa' => $viewData,
        ]);
    }

}
