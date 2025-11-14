<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use App\Models\TourCategory;
use App\Models\TourService;
use App\Support\Helpers\CurrencyHelper;
use Illuminate\Support\Facades\App;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class TourController extends Controller
{
    /**
     * Yardımcı: i18n kolonları normalize eder
     */
    private function localize($value)
    {
        if (!is_array($value)) {
            // string veya null ise direkt döndür
            return $value;
        }

        $locale = App::getLocale();

        $v = $value[$locale] ?? null;

        // string ise direkt döndür
        if (is_string($v)) {
            return $v;
        }

        // ARRAY ise → örn. notes: [{value:"..."}]
        if (is_array($v)) {
            return collect($v)
                ->pluck('value')
                ->filter()
                ->implode("\n"); // paragraf listesi gibi
        }

        return null;
    }

    /**
     * Tur kategori isim mapping (dinamik i18n için sadece anahtar)
     */
    private function mapDays($days)
    {
        if (!is_array($days)) {
            return [];
        }

        // Blade'de t('excursion.days.mon') şeklinde çevirilecek
        return array_map(fn ($d) => strtolower($d), $days);
    }

    /**
     * Tur görsel seti
     */
    private function mapMedia($mediaItems)
    {
        return collect($mediaItems)->map(function ($media) {
            return [
                'small'   => $media->getUrl('small'),
                'small2x' => $media->getUrl('small2x'),
                'large'   => $media->getUrl('large'),
                'large2x' => $media->getUrl('large2x'),
                'alt'     => $media->getCustomProperty('alt', ''),
            ];
        })->values()->all();
    }

    /**
     * Listeleme sayfası
     */
    public function index()
    {
        $locale = App::getLocale();

        $tours = Tour::with(['category'])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Tour $t) use ($locale) {

                $prices = $t->prices ?? [];

                return [
                    'id'            => $t->id,
                    'name'          => $this->localize($t->name),
                    'slug'          => $this->localize($t->slug),
                    'short_description' => $this->localize($t->short_description),
                    'category'      => $t->category?->name_l,
                    'category_slug' => $t->category?->slug_l,

                    // fiyat sadece adult
                    'prices'        => $prices,

                    // medya
                    'cover'         => optional($t->getFirstMedia('cover'), function ($m) {
                        return [
                            'small'   => $m->getUrl('small'),
                            'small2x' => $m->getUrl('small2x'),
                            'alt'     => $m->getCustomProperty('alt', ''),
                        ];
                    }),

                    'gallery'       => $this->mapMedia($t->getMedia('gallery')),
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
                'id'     => $cat->id,
                'name'   => $cat->name_l,
                'slug'   => $cat->slug_l,
            ])
            ->values();

        return view('pages.excursion.index', [
            'tours'      => $tours,
            'categories' => $categories,
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

        // Galeri (large / small)
        $gallery = $tour->getMedia('gallery')
            ->map(function (Media $media) {
                return [
                    'large'   => $media->getUrl('large'),
                    'large2x' => $media->getUrl('large2x'),
                    'small'   => $media->getUrl('small'),
                    'small2x' => $media->getUrl('small2x'),
                    'alt'     => $media->getCustomProperty('alt') ?: $media->name,
                ];
            })
            ->values()
            ->all();

        // Hizmet isimleri (dahil / hariç)
        $includedIds = $tour->included_service_ids ?? [];
        $excludedIds = $tour->excluded_service_ids ?? [];
        $allIds      = array_values(array_unique(array_merge($includedIds, $excludedIds)));

        $services = [];
        if (!empty($allIds)) {
            $services = TourService::query()
                ->whereIn('id', $allIds)
                ->get()
                ->keyBy('id');
        }

        $localizeService = function ($id) use ($services, $locale): ?string {
            $service = $services[$id] ?? null;
            if (!$service) {
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

        // COVER görseli (thumb + thumb2x)
        $cover = optional($tour->getFirstMedia('cover'), function ($m) {
            return [
                'thumb'   => $m->getUrl('thumb'),
                'thumb2x' => $m->getUrl('thumb2x'),
                'alt'     => $m->getCustomProperty('alt', ''),
            ];
        });

        $viewData = [
            'id'                  => $tour->id,
            'slug'                => $tour->slug[$locale] ?? null,
            'name'                => $this->localize($tour->name),
            'short_description'   => $this->localize($tour->short_description),
            'long_description'    => $this->localize($tour->long_description),
            'notes'               => $this->localize($tour->notes),
            'prices'              => $tour->prices ?? [],
            'duration'            => $tour->duration,
            'start_time'          => $tour->start_time ? $tour->start_time->format('H:i') : null,
            'min_age'             => $tour->min_age,
            'days_of_week'        => $tour->days_of_week ?? [],
            'category_name'       => $tour->category?->name_l,
            'category_slug'       => $tour->category?->slug_l,

            // YENİ EKLENDİ
            'cover'               => $cover,

            // Galeri
            'gallery'             => $gallery,

            'included_services'   => $includedServices,
            'excluded_services'   => $excludedServices,
        ];


        return view('pages.excursion.excursion-detail', [
            'tour'     => $viewData,
            'currency' => $currency,
        ]);
    }

}
