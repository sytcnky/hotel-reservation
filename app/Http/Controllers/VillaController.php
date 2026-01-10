<?php

namespace App\Http\Controllers;

use App\Models\StaticPage;
use App\Models\Villa;
use App\Services\CampaignPlacementViewService;
use App\Support\Helpers\I18nHelper;
use App\Support\Helpers\LocaleHelper;
use Illuminate\Http\Request;

class VillaController extends Controller
{
    /**
     * Villa listeleme
     */
    public function index(Request $request)
    {
        $locale       = app()->getLocale();              // uiLocale
        $baseLang     = LocaleHelper::defaultCode();     // baseLocale
        $currencyCode = \App\Support\Helpers\CurrencyHelper::currentCode();

        $villas = Villa::query()
            ->with(['location.parent.parent', 'category', 'media', 'rateRules.currency'])
            ->where('is_active', true)
            ->orderBy("name->{$locale}")
            ->get()
            ->map(function (Villa $villa) use ($locale, $baseLang, $currencyCode) {

                // İsim (jsonb)
                $name = I18nHelper::scalar($villa->name, $locale, $baseLang);

                // Slug (jsonb)
                $slug = I18nHelper::scalar($villa->slug, $locale, $baseLang);

                // Kapak görseli (standart accessor: normalize + placeholder + alt policy helper içinde)
                $cover = $villa->cover_image;

                // Lokasyon
                $area   = $villa->location;
                $city   = $area?->parent?->name;
                $region = $area?->parent?->parent?->name;

                // Fiyat (liste için helper)
                $price = $villa->getBasePrice($currencyCode);

                return [
                    'id'             => $villa->id,
                    'slug'           => $slug,
                    'name'           => $name ?? 'Villa', // mevcut davranış korunur
                    'max_guests'     => $villa->max_guests,
                    'bedroom_count'  => $villa->bedroom_count,
                    'bathroom_count' => $villa->bathroom_count,
                    'location'       => [
                        'city'   => $city,
                        'region' => $region,
                    ],
                    'category_name'  => $villa->category?->name_l,
                    'cover'          => $cover,
                    'price'          => $price,
                    'currency'       => $currencyCode,
                ];
            })
            ->values()
            ->all();

        // -------------------------------------------------
        // Static Page (Villa)
        // -------------------------------------------------

        $page = StaticPage::query()
            ->where('key', 'villa_page')
            ->where('is_active', true)
            ->firstOrFail();

        $base = LocaleHelper::defaultCode();
        $loc  = app()->getLocale();

        $pickLocale = function ($map) use ($loc, $base) {
            if (! is_array($map)) {
                return null;
            }

            return $map[$loc] ?? $map[$base] ?? null;
        };

        return view('pages.villa.index', [
            'villas'     => $villas,
            'page'       => $page,
            'pickLocale' => $pickLocale,
        ]);
    }

    /**
     * Villa detay
     */
    public function show(string $slug, CampaignPlacementViewService $campaignService)
    {
        $locale   = app()->getLocale();              // uiLocale
        $baseLang = LocaleHelper::defaultCode();     // baseLocale

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
        $name        = I18nHelper::scalar($villa->name, $locale, $baseLang) ?? 'Villa'; // mevcut davranış korunur
        $description = I18nHelper::scalar($villa->description, $locale, $baseLang);

        // Kategori → badge
        $categoryLabel = null;
        if ($villa->category) {
            $categoryLabel = I18nHelper::scalar($villa->category->name, $locale, $baseLang);
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

        // Görseller (standart accessor'lar)
        // - cover_image: normalize + placeholder + alt policy helper içinde
        // - gallery_images: normalize edilmiş dizi, BOŞ gelebilir
        // Gallery boşsa cover gösterme kararı controller'da değil, Blade tarafında ele alınacak.
        $cover   = $villa->cover_image;
        $gallery = $villa->gallery_images;

        // Yakındaki yerler
        $nearbyPlaces = [];
        foreach ((array) $villa->nearby as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label    = I18nHelper::scalar($item['label'] ?? null, $locale, $baseLang);
            $distance = I18nHelper::scalar($item['distance'] ?? null, $locale, $baseLang);

            if (! $label && ! $distance) {
                continue;
            }

            $nearbyPlaces[] = [
                'icon'  => $item['icon'] ?? 'fi-rr-marker',
                'label' => $label,
                'value' => $distance,
            ];
        }

        // Öne çıkanlar / konaklama bilgisi
        $highlights        = I18nHelper::stringList($villa->highlights, $locale, $baseLang);
        $accommodationInfo = I18nHelper::stringList($villa->stay_info, $locale, $baseLang);

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

            // NOTE: mevcut anahtarları koruyoruz; cover ek anahtar (blade sprintinde kullanırız)
            'cover'           => $cover,
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

            'promo_video_id' => $villa->promo_video_id,
            'nearby_places'  => $nearbyPlaces,
            'latitude'       => $villa->latitude,
            'longitude'      => $villa->longitude,
        ];

        return view('pages.villa.villa-detail', [
            'villa'     => $viewData,
            'campaigns' => $campaignService->buildForPlacement('villa_detail'),
        ]);
    }
}
