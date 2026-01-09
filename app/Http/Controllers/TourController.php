<?php

namespace App\Http\Controllers;

use App\Models\StaticPage;
use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourService;
use App\Services\CampaignPlacementViewService;
use App\Support\Helpers\CurrencyHelper;
use App\Support\Helpers\I18nHelper;
use App\Support\Helpers\LocaleHelper;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TourController extends Controller
{
    /**
     * Yardımcı: i18n kolonları normalize eder.
     *
     * Sprint standardı:
     * - uiLocale = app()->getLocale()
     * - baseLocale = LocaleHelper::defaultCode()
     * - pick = data[ui] ?? data[base] ?? ''
     *
     * Ayrıca bazı alanlar (notes gibi) locale içinde [{value:"..."}] liste olarak tutulabiliyor:
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
    private function mapDays($days)
    {
        if (! is_array($days)) {
            return [];
        }

        return array_map(fn ($d) => strtolower($d), $days);
    }

    /**
     * Ortak media map (ImageHelper.normalize)
     */
    private function mapMedia($mediaItems): array
    {
        return collect($mediaItems)
            ->map(function (Media $media) {
                return \App\Support\Helpers\ImageHelper::normalize($media);
            })
            ->values()
            ->all();
    }

    /**
     * Listeleme sayfası
     */
    public function index()
    {
        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();

        $page = StaticPage::query()
            ->where('key', 'tour_page')
            ->where('is_active', true)
            ->firstOrFail();

        $c   = $page->content ?? [];
        $loc = $uiLocale;

        $tours = Tour::with(['category'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Tour $t) use ($uiLocale, $baseLocale) {
                $prices   = $t->prices ?? [];
                $tourName = $this->localize($t->name, $uiLocale, $baseLocale);

                // Kapak (normalize + alt)
                $coverMedia = $t->getFirstMedia('cover');

                $cover = $coverMedia
                    ? \App\Support\Helpers\ImageHelper::normalize($coverMedia)
                    : null;

                if ($cover && $tourName) {
                    $cover['alt'] = $tourName;
                }

                return [
                    'id'                => $t->id,
                    'name'              => $tourName,
                    'slug'              => I18nHelper::scalar($t->slug, $uiLocale, $baseLocale),
                    'short_description' => $this->localize($t->short_description, $uiLocale, $baseLocale),
                    'category'          => $t->category?->name_l,
                    'category_slug'     => $t->category?->slug_l,
                    'prices'            => $prices,

                    // Kapak
                    'cover'             => $cover,

                    // Galeri (format aynı kalsın diye normalize edilmiş halde dönüyoruz)
                    'gallery'           => $this->mapMedia($t->getMedia('gallery')),
                ];
            });

        // Kategoriler (boş olmayanlar)
        $categories = TourCategory::where('is_active', true)
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
            'tours'       => $tours,
            'categories'  => $categories,
            'page'        => $page,
            'c'           => $c,
            'loc'         => $loc,
        ]);
    }

    /**
     * Detay
     */
    public function show(string $slug, CampaignPlacementViewService $campaignService)
    {
        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();
        $currency   = strtoupper(CurrencyHelper::currentCode());

        $tour = Tour::query()
            ->with(['category', 'media'])
            ->where('is_active', true)
            ->where("slug->{$uiLocale}", $slug)
            ->firstOrFail();

        $tourName = $this->localize($tour->name, $uiLocale, $baseLocale);

        // KAPAK (normalize + alt)
        $coverMedia = $tour->getFirstMedia('cover');

        $cover = $coverMedia
            ? \App\Support\Helpers\ImageHelper::normalize($coverMedia)
            : null;

        if ($cover && $tourName) {
            $cover['alt'] = $tourName;
        }

        // GALERİ (normalize + fallback mantığı)
        $galleryMedia = $tour->getMedia('gallery');

        if ($galleryMedia->isEmpty()) {
            if ($cover) {
                // Galeri yoksa kapak üzerinden tek elemanlı galeri
                $gallery = [$cover];
            } else {
                // Kapak da yoksa placeholder
                $placeholder = \App\Support\Helpers\ImageHelper::normalize(null);
                $placeholder['alt'] = $tourName ?? 'Tur görseli';
                $gallery = [$placeholder];
            }
        } else {
            $gallery = $this->mapMedia($galleryMedia);

            // Alt text boş gelmişse tur adıyla dolduralım (opsiyonel ama mantıklı)
            if ($tourName) {
                foreach ($gallery as &$img) {
                    if (empty($img['alt'])) {
                        $img['alt'] = $tourName;
                    }
                }
                unset($img);
            }
        }

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
            'prices'            => $tour->prices ?? [],
            'duration'          => $tour->duration,
            'start_time'        => $tour->start_time ? $tour->start_time->format('H:i') : null,
            'min_age'           => $tour->min_age,
            'days_of_week'      => $tour->days_of_week ?? [],
            'category_name'     => $tour->category?->name_l,
            'category_slug'     => $tour->category?->slug_l,

            // Kapak + galeri (normalize edilmiş)
            'cover'             => $cover,
            'gallery'           => $gallery,

            'included_services' => $includedServices,
            'excluded_services' => $excludedServices,
        ];

        return view('pages.excursion.excursion-detail', [
            'tour'      => $viewData,
            'currency'  => $currency,
            'campaigns' => $campaignService->buildForPlacement('tour_detail'),
        ]);
    }
}
