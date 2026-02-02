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
    public static function get(string $baseName, $action): array
    {
        return self::map('get', $baseName, $action);
    }

    /**
     * @return array<int, LaravelRoute>
     */
    public static function post(string $baseName, $action): array
    {
        return self::map('post', $baseName, $action);
    }

    /**
     * @return array<int, LaravelRoute>
     */
    public static function put(string $baseName, $action): array
    {
        return self::map('put', $baseName, $action);
    }

    /**
     * @return array<int, LaravelRoute>
     */
    public static function patch(string $baseName, $action): array
    {
        return self::map('patch', $baseName, $action);
    }

    /**
     * @return array<int, LaravelRoute>
     */
    public static function delete(string $baseName, $action): array
    {
        return self::map('delete', $baseName, $action);
    }

    /**
     * @return array<int, LaravelRoute>
     */
    public static function view(string $baseName, string $view, array $data = []): array
    {
        return self::map('view', $baseName, [$view, $data]);
    }

    /**
     * Ortak mapper. Fallback yok (fail-fast).
     * İstisna: home route'u locale root olduğu için slug boş olabilir.
     *
     * @return array<int, LaravelRoute> Her locale için üretilen route nesneleri
     */
    protected static function map(string $method, string $baseName, $payload): array
    {
        $locales = LocaleHelper::active();

        $translation = Translation::query()
            ->where('group', 'routes')
            ->where('key', $baseName)
            ->first();

        $values = is_array($translation?->values ?? null) ? $translation->values : null;

        if (! is_array($values)) {
            throw new \RuntimeException("Missing routes translation record: group=routes, key={$baseName}");
        }

        $routes = [];

        foreach ($locales as $locale) {
            $slugRaw = $values[$locale] ?? null;

            if (! is_string($slugRaw)) {
                throw new \RuntimeException("Missing routes translation value: group=routes, key={$baseName}, locale={$locale}");
            }

            // Allow empty only for home (locale root)
            $slug = trim($slugRaw);

            if ($slug === '' && $baseName !== 'home') {
                throw new \RuntimeException("Empty routes translation value: group=routes, key={$baseName}, locale={$locale}");
            }

            $slug = trim($slug, '/');

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
