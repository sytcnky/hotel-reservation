<?php

namespace App\Support\Routing;

use App\Models\Translation;
use App\Support\Helpers\LocaleHelper;
use Illuminate\Support\Facades\Route;

class LocalizedRoute
{
    /**
     * Çok dilli GET route.
     *
     * @param  string                $baseName   Base route adı (ör: 'home', 'hotels.index')
     * @param  string                $defaultSlug Varsayılan slug (çeviri yoksa kullanılacak), prefixsiz (ör: 'hotels')
     * @param  callable|array|string $action     Route action (closure, [Controller::class, 'method'], 'Controller@method')
     */
    public static function get(string $baseName, string $defaultSlug, $action): void
    {
        self::map('get', $baseName, $defaultSlug, $action);
    }

    /**
     * Çok dilli POST route.
     *
     * @param  string                $baseName
     * @param  string                $defaultSlug
     * @param  callable|array|string $action
     */
    public static function post(string $baseName, string $defaultSlug, $action): void
    {
        self::map('post', $baseName, $defaultSlug, $action);
    }

    /**
     * Çok dilli PUT route.
     *
     * @param  string                $baseName
     * @param  string                $defaultSlug
     * @param  callable|array|string $action
     */
    public static function put(string $baseName, string $defaultSlug, $action): void
    {
        self::map('put', $baseName, $defaultSlug, $action);
    }

    /**
     * Çok dilli PATCH route.
     *
     * @param  string                $baseName
     * @param  string                $defaultSlug
     * @param  callable|array|string $action
     */
    public static function patch(string $baseName, string $defaultSlug, $action): void
    {
        self::map('patch', $baseName, $defaultSlug, $action);
    }

    /**
     * Çok dilli DELETE route.
     *
     * @param  string                $baseName
     * @param  string                $defaultSlug
     * @param  callable|array|string $action
     */
    public static function delete(string $baseName, string $defaultSlug, $action): void
    {
        self::map('delete', $baseName, $defaultSlug, $action);
    }

    /**
     * Çok dilli view route (statik sayfalar için).
     *
     * @param string $baseName
     * @param string $defaultSlug
     * @param string $view
     * @param array  $data
     */
    public static function view(string $baseName, string $defaultSlug, string $view, array $data = []): void
    {
        self::map('view', $baseName, $defaultSlug, [$view, $data]);
    }

    /**
     * Ortak mapper.
     *
     * Her aktif locale için:
     *   - slug'ı Translation(group='routes', key=$baseName) içinden okur.
     *   - yoksa $defaultSlug kullanır.
     *   - URL: /{locale}/{slug}
     *   - name: {locale}.{baseName}
     */
    protected static function map(string $method, string $baseName, string $defaultSlug, $payload): void
    {
        $locales = LocaleHelper::active(); // ['tr', 'en', ...] bekliyoruz

        // İlgili route için slug çevirileri
        $translation = Translation::query()
            ->where('group', 'routes')
            ->where('key', $baseName)
            ->first();

        foreach ($locales as $locale) {
            // 1) Çeviriden slug al, yoksa default kullan
            $slug = $translation->values[$locale] ?? $defaultSlug;
            $slug = trim($slug, '/'); // sadece path, prefix yok

            // Home gibi boş slug ise sadece /{locale}
            $path = $slug === '' ? '/' : '/' . $slug;

            // 2) Prefix + name group
            Route::prefix($locale)
                ->name($locale . '.')
                ->group(function () use ($method, $path, $baseName, $payload) {
                    // Aynı baseName her locale için kullanılır; group name ile birleşir.
                    if ($method === 'view') {
                        [$view, $data] = $payload;
                        $route = Route::view($path, $view, $data);
                    } else {
                        $route = Route::$method($path, $payload);
                    }

                    $route->name($baseName);
                });
        }
    }
}
