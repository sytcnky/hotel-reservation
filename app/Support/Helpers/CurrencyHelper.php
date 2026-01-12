<?php

namespace App\Support\Helpers;

use App\Models\Currency;
use App\Support\Currency\CurrencyContext;
use Illuminate\Support\Collection;

class CurrencyHelper
{
    /**
     * Header dropdown için seçenek listesi.
     * [
     *   ['code' => 'TRY', 'label' => 'Türk Lirası', 'symbol' => '₺'],
     *   ...
     * ]
     */
    public static function activeOptions(): array
    {
        return CurrencyContext::options();
    }

    /**
     * Geriye dönük kullanım için: aktif currency modelleri (Collection<Currency>).
     */
    public static function active(): Collection
    {
        return Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Geriye dönük: header meta map (code => meta).
     */
    public static function options(): array
    {
        return collect(self::activeOptions())
            ->mapWithKeys(fn (array $c) => [
                $c['code'] => [
                    'code'   => $c['code'],
                    'label'  => $c['label'] ?? $c['code'],
                    'symbol' => $c['symbol'] ?? null,
                ],
            ])
            ->toArray();
    }

    /**
     * Fallback yok (TRY/config yok).
     * Çözümler:
     * - user/session/locale->language.currency/settings.default_currency
     * - yoksa null
     */
    public static function currentCode(): ?string
    {
        return CurrencyContext::code();
    }

    public static function exists(string $code): bool
    {
        $code = strtoupper(trim($code));
        if ($code === '') {
            return false;
        }

        return Currency::query()
            ->where('is_active', true)
            ->where('code', $code)
            ->exists();
    }
}
