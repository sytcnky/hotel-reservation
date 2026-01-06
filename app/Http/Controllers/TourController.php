<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourService;
use App\Support\Helpers\CurrencyHelper;
use Illuminate\Support\Facades\App;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\StaticPage;

class TourController extends Controller
{
    /**
     * Yardımcı: i18n kolonları normalize eder
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
        $locale = App::getLocale();

        $page = StaticPage::query()
            ->where('key', 'tour_page')
            ->where('is_active', true)
            ->firstOrFail();

        $c = $page->content ?? [];
        $loc = app()->getLocale();

        $tours = Tour::with(['category'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Tour $t) use ($locale) {

                $prices   = $t->prices ?? [];
                $tourName = $this->localize($t->name);

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
                    'slug'              => $t->slug[$locale] ?? null,
                    'short_description' => $this->localize($t->short_description),
                    'category'          => $t->category?->name_l,
                    'category_slug'     => $t->category?->slug_l,
                    'prices'            => $prices,

                    // Kapak
                    'cover'             => $cover,

                    // Galeri (şimdilik sadece detayta kullanıyoruz ama
                    // format aynı kalsın diye normalize edilmiş halde dönüyoruz)
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
            'tours'      => $tours,
            'categories' => $categories,
            'page' => $page,
            'c' => $c,
            'loc' => $loc,
        ]);
    }

    /**
     * Detay
     */
    public function show(string $slug)
    {
        $locale   = app()->getLocale();
        $currency = strtoupper(CurrencyHelper::currentCode());

        $tour = Tour::query()
            ->with(['category', 'media'])
            ->where('is_active', true)
            ->where("slug->{$locale}", $slug)
            ->firstOrFail();

        $tourName = $this->localize($tour->name);

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

        $localizeService = function ($id) use ($services, $locale): ?string {
            $service = $services[$id] ?? null;
            if (! $service) {
                return null;
            }

            $name = $service->name;
            if (is_array($name)) {
                return $name[$locale] ?? null;
            }

            return (string) $name;
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
            'slug'              => $tour->slug[$locale] ?? null,
            'name'              => $tourName,
            'short_description' => $this->localize($tour->short_description),
            'long_description'  => $this->localize($tour->long_description),
            'notes'             => $this->localize($tour->notes),
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
            'tour'     => $viewData,
            'currency' => $currency,
        ]);
    }
}
