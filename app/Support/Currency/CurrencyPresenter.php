<?php

namespace App\Support\Currency;

use App\Models\Currency;
use App\Models\Setting;
use NumberFormatter;

class CurrencyPresenter
{
    public const DASH = '—';

    /**
     * Header/switch gibi amount olmayan yerler için:
     * currency_display=code  => "TRY"
     * currency_display=symbol=> "₺" (yoksa "TRY")
     */
    public static function label(?string $code): string
    {
        $code = self::normalizeCode($code);
        if ($code === null) {
            return self::DASH;
        }

        $meta = self::meta($code);
        if (! $meta) {
            return self::DASH;
        }

        $mode = self::displayMode();

        if ($mode === 'code') {
            return $code;
        }

        // mode = symbol
        $symbol = trim((string) ($meta['symbol'] ?? ''));
        return $symbol !== '' ? $symbol : $code;
    }

    /**
     * Fiyat gösterimi:
     * - ICU NumberFormatter ile locale-aware sayı
     * - exponent kadar basamak + half-up
     * - currency_display ile (symbol|code) seçimi label() üzerinden
     * - affix_position ile prefix|suffix yerleşim
     */
    public static function format($amount, ?string $code): string
    {
        $code = self::normalizeCode($code);
        if ($code === null) {
            return self::DASH;
        }

        if ($amount === null || $amount === '') {
            return self::DASH;
        }

        $amount = (float) $amount;

        $meta = self::meta($code);
        if (! $meta) {
            return self::DASH;
        }

        $exp = (int) ($meta['exponent'] ?? 2);
        if ($exp < 0) {
            $exp = 0;
        }

        $rounded = round($amount, $exp, PHP_ROUND_HALF_UP);
        $number  = self::formatNumberLocaleAware($rounded, $exp);

        // ICU yoksa fallback YOK => —
        if ($number === self::DASH) {
            return self::DASH;
        }

        $label = self::label($code);
        if ($label === self::DASH) {
            return self::DASH;
        }

        $pos = strtolower(trim((string) ($meta['affix_position'] ?? 'suffix')));
        $pos = in_array($pos, ['prefix', 'suffix'], true) ? $pos : 'suffix';

        return $pos === 'prefix'
            ? ($label . ' ' . $number)
            : ($number . ' ' . $label);
    }

    /**
     * Sadece aktif currency meta okunur.
     */
    public static function meta(?string $code): ?array
    {
        $code = self::normalizeCode($code);
        if ($code === null) {
            return null;
        }

        $c = Currency::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (! $c) {
            return null;
        }

        return [
            'code'           => (string) $c->code,
            'symbol'         => $c->symbol !== null ? (string) $c->symbol : null,
            'exponent'       => (int) $c->exponent,
            'affix_position' => $c->affix_position !== null ? (string) $c->affix_position : null,
        ];
    }

    private static function displayMode(): string
    {
        // Helper’a değil, kanıtlanmış API’ye bağlanıyoruz.
        $v = (string) Setting::get('currency_display', 'code');
        $v = strtolower(trim($v));

        return in_array($v, ['symbol', 'code'], true) ? $v : 'code';
    }

    private static function normalizeCode(?string $code): ?string
    {
        $code = strtoupper(trim((string) $code));
        return $code === '' ? null : $code;
    }

    private static function formatNumberLocaleAware(float $amount, int $fractionDigits): string
    {
        $locale = app()->getLocale() ?: 'en';

        try {
            $fmt = new NumberFormatter($locale, NumberFormatter::DECIMAL);
            $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, $fractionDigits);
            $fmt->setAttribute(NumberFormatter::ROUNDING_MODE, NumberFormatter::ROUND_HALFUP);

            $out = $fmt->format($amount);

            if (is_string($out) && $out !== '') {
                return $out;
            }
        } catch (\Throwable) {
            // fallback YOK
        }

        return self::DASH;
    }

    /**
     * Admin Panel için bara birimi
     */
    public static function labelAdmin(?string $code): string
    {
        $code = self::normalizeCode($code);
        return $code ?? self::DASH;
    }

    /**
     * Admin Panel için bara number format
     */
    public static function formatAdmin($amount, ?string $code): string
    {
        $code = self::normalizeCode($code);
        if ($code === null) {
            return self::DASH;
        }

        if ($amount === null || $amount === '') {
            return self::DASH;
        }

        $amount = (float) $amount;

        // Admin: her zaman 2 basamak
        $rounded = round($amount, 2, PHP_ROUND_HALF_UP);
        $number  = self::formatNumberLocaleAware($rounded, 2);

        if ($number === self::DASH) {
            return self::DASH;
        }

        return $number . ' ' . $code; // Admin: always suffix + code
    }

}
