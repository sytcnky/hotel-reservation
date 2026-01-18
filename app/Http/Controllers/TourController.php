<?php

namespace App\Http\Controllers;

use App\Models\StaticPage;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourService;
use App\Services\CampaignPlacementViewService;
use App\Support\Currency\CurrencyContext;
use App\Support\Helpers\I18nHelper;
use App\Support\Helpers\LocaleHelper;

class TourController extends Controller
{
    /**
     * Yardımcı: i18n kolonları normalize eder.
     *
     * Standart:
     * - uiLocale = app()->getLocale()
     * - baseLocale = LocaleHelper::defaultCode()
     * - pick = data[ui] ?? data[base] ?? ...
     *
     * Not: Bazı alanlar (notes gibi) locale içinde [{value:"..."}] liste olarak tutulabiliyor.
     * - Bu durumda value'ları satır satır birleştirir.
     */
    private function localize(mixed $value, string $uiLocale, string $baseLocale): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $picked = I18nHelper::pick($value, $uiLocale, $baseLocale);

        if (is_string($picked)) {
            return $picked;
        }

        if (is_scalar($picked)) {
            return (string) $picked;
        }

        if (is_array($picked)) {
            return collect($picked)
                ->map(function ($item) {
                    if (is_array($item)) {
                        return $item['value'] ?? null;
                    }

                    return $item;
                })
                ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                ->implode("\n");
        }

        return null;
    }

    /**
     * Tur günlerini map eder (şimdilik sadece lower-case)
     */
    private function mapDays($days): array
    {
        if (! is_array($days)) {
            return [];
        }

        return array_map(fn ($d) => strtolower($d), $days);
    }

    /**
     * UI currency için "adult" fiyatını çözer.
     * - Hardcode TRY yok.
     * - firstCurrency fallback yok.
     * - currency yoksa veya o currency altında adult yoksa null döner.
     */
    private function resolveAdultPriceForCurrency(array $prices, ?string $currencyCode): array
    {
        $code = strtoupper(trim((string) $currencyCode));
        if ($code === '' || empty($prices)) {
            return ['currency' => null, 'adult' => null];
        }

        $cfg = $prices[$code] ?? null;
        if (! is_array($cfg)) {
            return ['currency' => null, 'adult' => null];
        }

        $adult = $cfg['adult'] ?? null;
        if (! is_numeric($adult)) {
            return ['currency' => null, 'adult' => null];
        }

        $adult = (float) $adult;
        if ($adult <= 0) {
            return ['currency' => null, 'adult' => null];
        }

        return ['currency' => $code, 'adult' => $adult];
    }

    /**
     * Listeleme sayfası
     */
    public function index()
    {
        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();
        $uiCurrency = CurrencyContext::code();

        $page = StaticPage::query()
            ->where('key', 'tour_page')
            ->where('is_active', true)
            ->firstOrFail();

        $c   = $page->content ?? [];
        $loc = $uiLocale;

        $tours = Tour::query()
            ->with(['category', 'media'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Tour $t) use ($uiLocale, $baseLocale, $uiCurrency) {
                $prices   = $t->prices ?? [];
                $tourName = $this->localize($t->name, $uiLocale, $baseLocale);

                $resolved = $this->resolveAdultPriceForCurrency(
                    is_array($prices) ? $prices : [],
                    $uiCurrency
                );

                // NOTE:
                // - cover_image accessor'ı her zaman ImageHelper::normalize(...) formatı döner (yoksa placeholder).
                // - gallery_images accessor'ı yoksa [] döner.
                // - Controller'da alt/placeholder/fallback üretilmez.
                return [
                    'id'                => $t->id,
                    'name'              => $tourName,
                    'slug'              => I18nHelper::scalar($t->slug, $uiLocale, $baseLocale),
                    'short_description' => $this->localize($t->short_description, $uiLocale, $baseLocale),
                    'category'          => $t->category?->name_l,
                    'category_slug'     => $t->category?->slug_l,

                    // Fiyatlar (ham + UI currency için çözülmüş adult)
                    'prices'            => is_array($prices) ? $prices : [],
                    'ui_currency'       => $resolved['currency'],
                    'adult_price'       => $resolved['adult'],

                    // Media (standart accessor)
                    'cover'             => $t->cover_image,
                    'gallery'           => $t->gallery_images,
                ];
            });

        // Kategoriler (boş olmayanlar)
        $categories = TourCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->filter(function ($cat) use ($tours) {
                return $tours->contains(fn ($t) => $t['category'] === $cat->name_l);
            })
            ->map(fn ($cat) => [
                'id'   => $cat->id,
                'name' => $cat->name_l,
                'slug' => $cat->slug_l,
            ])
            ->values();

        return view('pages.excursion.index', [
            'tours'      => $tours,
            'categories' => $categories,
            'page'       => $page,
            'c'          => $c,
            'loc'        => $loc,
        ]);
    }

    /**
     * Detay
     */
    public function show(string $slug, CampaignPlacementViewService $campaignService)
    {
        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();
        $uiCurrency = CurrencyContext::code();

        $tour = Tour::query()
            ->with(['category', 'media'])
            ->where('is_active', true)
            ->where("slug->{$uiLocale}", $slug)
            ->firstOrFail();

        $tourName = $this->localize($tour->name, $uiLocale, $baseLocale);

        // NOTE:
        // - Gallery boşsa cover gösterimi Blade'de çözülecek.
        // - cover_image accessor'ı placeholder döndürdüğü için "cover boşsa placeholder" otomatik.
        $cover   = $tour->cover_image;
        $gallery = $tour->gallery_images;

        $pricesRaw = $tour->prices ?? [];
        $pricesRaw = is_array($pricesRaw) ? $pricesRaw : [];

        $resolved = $this->resolveAdultPriceForCurrency($pricesRaw, $uiCurrency);

        // Hizmet isimleri (dahil / hariç)
        $includedIds = $tour->included_service_ids ?? [];
        $excludedIds = $tour->excluded_service_ids ?? [];
        $allIds      = array_values(array_unique(array_merge($includedIds, $excludedIds)));

        $services = [];
        if (! empty($allIds)) {
            $services = TourService::query()
                ->whereIn('id', $allIds)
                ->get()
                ->keyBy('id');
        }

        $localizeService = function ($id) use ($services, $uiLocale, $baseLocale): ?string {
            $service = $services[$id] ?? null;
            if (! $service) {
                return null;
            }

            return I18nHelper::scalar($service->name, $uiLocale, $baseLocale);
        };

        $includedServices = collect($includedIds)
            ->map(fn ($id) => $localizeService($id))
            ->filter()
            ->values()
            ->all();

        $excludedServices = collect($excludedIds)
            ->map(fn ($id) => $localizeService($id))
            ->filter()
            ->values()
            ->all();

        $viewData = [
            'id'                => $tour->id,
            'slug'              => I18nHelper::scalar($tour->slug, $uiLocale, $baseLocale),
            'name'              => $tourName,
            'short_description' => $this->localize($tour->short_description, $uiLocale, $baseLocale),
            'long_description'  => $this->localize($tour->long_description, $uiLocale, $baseLocale),
            'notes'             => $this->localize($tour->notes, $uiLocale, $baseLocale),

            // Fiyatlar (ham + UI currency için çözülmüş adult)
            'prices'            => $pricesRaw,
            'ui_currency'       => $resolved['currency'],
            'adult_price'       => $resolved['adult'],

            'duration'          => $tour->duration,
            'start_time'        => $tour->start_time, // cast: datetime:H:i (string)
            'min_age'           => $tour->min_age,
            'days_of_week'      => $tour->days_of_week ?? [],
            'category_name'     => $tour->category?->name_l,
            'category_slug'     => $tour->category?->slug_l,

            // Media (standart accessor)
            'cover'             => $cover,
            'gallery'           => $gallery,

            'included_services' => $includedServices,
            'excluded_services' => $excludedServices,
        ];

        return view('pages.excursion.excursion-detail', [
            'tour'      => $viewData,
            'currency'  => $resolved['currency'], // Blade eski değişkeni bekliyorsa uyum için
            'campaigns' => $campaignService->buildForPlacement('tour_detail'),
        ]);
    }
}
