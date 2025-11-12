<?php

namespace App\Support\Helpers;

use App\Models\Currency;

class CurrencyHelper
{
    public static function options(): array
    {
        // key => meta (header dropdown için)
        return self::active()
            ->mapWithKeys(function (Currency $c) {
                return [
                    $c->code => [
                        'code'   => $c->code,
                        'label'  => $c->name ?? $c->code,
                        'symbol' => $c->symbol,
                    ],
                ];
            })
            ->toArray();
    }

    public static function active()
    {
        return Currency::query()
            ->where('is_active', true)
            ->get();
    }

    public static function currentCode(): string
    {
        if (auth()->check() && auth()->user()->currency) {
            return auth()->user()->currency;
        }

        if ($code = session('currency')) {
            return $code;
        }

        return config('app.currency', 'TRY'); // varsayılan
    }

    public static function exists(string $code): bool
    {
        return Currency::query()
            ->where('is_active', true)
            ->where('code', $code)
            ->exists();
    }
}
