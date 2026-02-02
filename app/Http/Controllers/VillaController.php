<?php

namespace App\Http\Controllers;

use App\Models\StaticPage;
use App\Models\Villa;
use App\Services\CampaignPlacementViewService;
use App\Services\VillaRateRuleSelector;
use App\Support\Currency\CurrencyContext;
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
        $currencyCode = CurrencyContext::code($request);

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
                $price = null;
                if ($currencyCode) {
                    $price = $villa->getBasePrice($currencyCode);
                }

                return [
                    'id'             => $villa->id,
                    'slug'           => $slug,
                    'name'           => $name, // mevcut davranış korunur
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
    public function show(
        Request $request,
        string $slug,
        CampaignPlacementViewService $campaignService,
        VillaRateRuleSelector $ruleSelector
    ) {
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
            ->whereRaw("NULLIF(slug->>?, '') = ?", [$locale, $slug])
            ->where('is_active', true)
            ->firstOrFail();

        $location = $villa->location;

        // Şehir / bölge
        $area     = $location?->name;
        $district = $location?->parent?->name;

        // İsim / açıklama i18n
        $name        = I18nHelper::scalar($villa->name, $locale, $baseLang);
        $description = I18nHelper::scalar($villa->description, $locale, $baseLang);

        // Kategori → badge
        $categoryLabel = null;
        if ($villa->category) {
            $categoryLabel = I18nHelper::scalar($villa->category->name, $locale, $baseLang);
        }

        // === FİYAT + MIN/MAX NIGHTS (single authority: selector) ===
        $selectedCurrency = CurrencyContext::code($request);
        $selectedCurrency = $selectedCurrency !== null ? strtoupper(trim($selectedCurrency)) : null;

        $rule = null;
        if ($selectedCurrency) {
            $rule = $ruleSelector->select($villa, $selectedCurrency);
        }

        $basePrice = null;
        $currency  = $selectedCurrency;
        $minNights = null;
        $maxNights = null;

        if ($rule && (float) $rule->amount > 0) {
            $basePrice = (float) $rule->amount;
            $currency  = $rule->currency?->code ? strtoupper(trim((string) $rule->currency->code)) : $selectedCurrency;
            $minNights = $rule->min_nights ?: null;
            $maxNights = $rule->max_nights ?: null;
        }

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
            'slug'           => $villa->slug[$locale] ?? null,
            'name'           => $name,

            'max_guests'     => $villa->max_guests,
            'bedroom_count'  => $villa->bedroom_count,
            'bathroom_count' => $villa->bathroom_count,

            'location' => [
                'area'     => $area,
                'district' => $district,
            ],

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
