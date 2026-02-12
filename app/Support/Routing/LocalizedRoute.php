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
     * Ortak mapper.
     *
     * Politika:
     * - Fallback YOK.
     * - Eksik routes çevirisi varsa uygulama ayağa kalksın:
     *   - O route / locale için route register edilmez -> doğal 404
     *   - Uyarı loglanır (local'de STDERR'e de basılır)
     *
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

        // Kayıt yoksa: hiç route üretme (tüm locale'lerde 404) + uyarı
        if (! is_array($values)) {
            self::reportIssue('missing_record', $baseName);

            return [];
        }

        $routes = [];

        foreach ($locales as $locale) {
            $slugRaw = $values[$locale] ?? null;

            // Bu locale için value yoksa: sadece bu locale'i atla + uyarı
            if (! is_string($slugRaw)) {
                self::reportIssue('missing_locale_value', $baseName, (string) $locale);
                continue;
            }

            // Allow empty only for home (locale root)
            $slug = trim($slugRaw);

            if ($slug === '' && $baseName !== 'home') {
                self::reportIssue('empty_locale_value', $baseName, (string) $locale);
                continue;
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

    private static function reportIssue(string $type, string $baseName, ?string $locale = null): void
    {
        $msg = "[routes-i18n] {$type}: group=routes key={$baseName}" . ($locale ? " locale={$locale}" : "");

        try {
            logger()->warning($msg, [
                'group'  => 'routes',
                'key'    => $baseName,
                'locale' => $locale,
                'type'   => $type,
            ]);
        } catch (\Throwable) {
            // ignore
        }

        // Local’de hızlı görünürlük için terminale bas (ilk requestte görünür)
        if (app()->environment('local')) {
            try {
                @fwrite(STDERR, $msg . PHP_EOL);
            } catch (\Throwable) {
                // ignore
            }
        }
    }
}
