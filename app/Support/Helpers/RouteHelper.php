<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('localized_route')) {
    /**
     * Base route adı üzerinden, istenen locale'e göre isimli route üretir.
     *
     * Örnek:
     *  - localized_route('home')                     -> aktif locale'e göre
     *  - localized_route('home', [], true, 'en')     -> en.home
     *  - localized_route('excursions.detail', ['slug' => 'marmaris-boat'], true, 'tr')
     */
    function localized_route(
        string $baseName,
        array $params = [],
        bool $absolute = true,
        ?string $forceLocale = null
    ): string {
        $locale = $forceLocale ?: (app()->getLocale() ?: config('app.locale', 'tr'));

        $name = $locale . '.' . $baseName;

        if (! Route::has($name)) {
            throw new InvalidArgumentException("Localized route [$name] not defined.");
        }

        return route($name, $params, $absolute);
    }
}

if (! function_exists('locale_switch_url')) {
    function locale_switch_url(string $targetLocale): string
    {
        $route         = request()->route();
        $currentLocale = app()->getLocale();

        // Route yoksa (çok düşük ihtimal) direkt hedef dilde home'a gitsin
        if (! $route) {
            $targetUrl = route($targetLocale . '.home');

            return route('locale.switch', [
                'locale'   => $targetLocale,
                'redirect' => $targetUrl,
            ]);
        }

        $fullName = $route->getName();              // örn: "tr.excursions.detail"
        $parts    = explode('.', $fullName, 2);
        $baseName = $parts[1] ?? $fullName;         // "excursions.detail"
        $params   = $route->parameters();

        // --- Tur detay özel durumu: slug i18n ---
        if ($baseName === 'excursions.detail' && isset($params['slug'])) {
            $currentSlug = $params['slug'];

            $tour = \App\Models\Tour::query()
                ->where("slug->{$currentLocale}", $currentSlug)
                ->first();

            if ($tour && is_array($tour->slug) && ! empty($tour->slug[$targetLocale])) {
                $params['slug'] = $tour->slug[$targetLocale];
            } else {
                // hedef dilde slug yoksa listeye düş
                $targetName = $targetLocale . '.excursions';
                $targetUrl  = route($targetName);

                return route('locale.switch', [
                    'locale'   => $targetLocale,
                    'redirect' => $targetUrl,
                ]);
            }
        }

        // Genel durum: {targetLocale}.{baseName}
        $targetName = $targetLocale . '.' . $baseName;
        if (Route::has($targetName)) {
            $targetUrl = route($targetName, $params);
        } else {
            // bu isim yoksa en azından /{locale} aç
            $targetUrl = '/' . $targetLocale;
        }

        return route('locale.switch', [
            'locale'   => $targetLocale,
            'redirect' => $targetUrl,
        ]);
    }
}

