<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('localized_route')) {
    /**
     * Base route adı üzerinden, aktif locale'e göre isimli route üretir.
     *
     * Örnek:
     *  - localized_route('home')         -> route('tr.home') / route('en.home')
     *  - localized_route('hotel.detail', ['id' => 5])
     *      -> route('tr.hotel.detail', ['id' => 5])
     */
    function localized_route(string $baseName, array $params = [], bool $absolute = true): string
    {
        $locale = app()->getLocale() ?: config('app.locale', 'tr');

        $name = $locale . '.' . $baseName;

        if (! Route::has($name)) {
            throw new InvalidArgumentException("Localized route [$name] not defined.");
        }

        return route($name, $params, $absolute);
    }
}
