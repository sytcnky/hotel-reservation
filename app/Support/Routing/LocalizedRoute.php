<?php

namespace App\Support\Routing;

use App\Models\Translation;
use App\Support\Helpers\LocaleHelper;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;

class LocalizedRoute
{
    /**
     * @return array<int, LaravelRoute>
     */
    public static function get(string $baseName, string $defaultSlug, $action): array
    {
        return self::map('get', $baseName, $defaultSlug, $action);
    }

    /**
     * @return array<int, LaravelRoute>
     */
    public static function post(string $baseName, string $defaultSlug, $action): array
    {
        return self::map('post', $baseName, $defaultSlug, $action);
    }

    /**
     * @return array<int, LaravelRoute>
     */
    public static function put(string $baseName, string $defaultSlug, $action): array
    {
        return self::map('put', $baseName, $defaultSlug, $action);
    }

    /**
     * @return array<int, LaravelRoute>
     */
    public static function patch(string $baseName, string $defaultSlug, $action): array
    {
        return self::map('patch', $baseName, $defaultSlug, $action);
    }

    /**
     * @return array<int, LaravelRoute>
     */
    public static function delete(string $baseName, string $defaultSlug, $action): array
    {
        return self::map('delete', $baseName, $defaultSlug, $action);
    }

    /**
     * @return array<int, LaravelRoute>
     */
    public static function view(string $baseName, string $defaultSlug, string $view, array $data = []): array
    {
        return self::map('view', $baseName, $defaultSlug, [$view, $data]);
    }

    /**
     * Ortak mapper.
     *
     * @return array<int, LaravelRoute> Her locale için üretilen route nesneleri
     */
    protected static function map(string $method, string $baseName, string $defaultSlug, $payload): array
    {
        $locales = LocaleHelper::active();

        $translation = Translation::query()
            ->where('group', 'routes')
            ->where('key', $baseName)
            ->first();

        $routes = [];

        foreach ($locales as $locale) {
            $slug = $translation->values[$locale] ?? $defaultSlug;
            $slug = trim((string) $slug, '/');

            $path = $slug === '' ? '/' : '/' . $slug;

            Route::prefix($locale)
                ->name($locale . '.')
                ->group(function () use ($method, $path, $baseName, $payload, &$routes) {
                    if ($method === 'view') {
                        [$view, $data] = $payload;

                        $route = Route::get($path, function () use ($view, $data) {
                            return view($view, $data);
                        });
                    } else {
                        $route = Route::$method($path, $payload);
                    }

                    $route->name($baseName);

                    $routes[] = $route;
                });
        }

        return $routes;
    }
}
