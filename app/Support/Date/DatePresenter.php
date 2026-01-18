<?php

namespace App\Support\Date;

use Carbon\Carbon;
use Carbon\CarbonInterface;

final class DatePresenter
{
    /**
     * Civil date (Y-m-d) -> human readable localized string.
     *
     * Kontrat:
     * - Input: strict Y-m-d (aksi durumda boş string döner; Blade kırılmaz)
     * - Blade içinde Carbon::parse yasak; sadece burası parse eder.
     */
    public static function human(?string $ymd, ?string $locale = null, string $pattern = 'd F Y'): string
    {
        $ymd = is_string($ymd) ? trim($ymd) : null;

        if ($ymd === null || $ymd === '') {
            return '';
        }

        // Strict Y-m-d guard (legacy parse yok)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $ymd) !== 1) {
            return '';
        }

        try {
            $dt = Carbon::createFromFormat('Y-m-d', $ymd)->startOfDay();
        } catch (\Throwable) {
            return '';
        }

        $loc = self::resolveLocale($locale);

        return $dt->locale($loc)->translatedFormat($pattern);
    }

    public static function humanShort(?string $ymd, ?string $locale = null): string
    {
        return self::human($ymd, $locale, 'd M Y');
    }

    /**
     * Instant datetime string -> human readable localized string.
     *
     * Sprint D kontratı:
     * - Input: string (created_at benzeri datetime taşıyan alanlar / filtre input'ları / legacy string)
     * - Blade/Model içinde Carbon::parse() dağınıklığı yerine tek otorite
     * - Parse edilemezse boş string döner (UI kırılmaz)
     */
    public static function humanDateTimeFromString(?string $value, ?string $locale = null, string $pattern = 'd.m.Y H:i'): string
    {
        $value = is_string($value) ? trim($value) : null;

        if ($value === null || $value === '') {
            return '';
        }

        $tz  = (string) config('app.timezone', 'Europe/Istanbul');
        $loc = self::resolveLocale($locale);

        try {
            // Not: parse başarı kriteri Carbon'a bırakılır; burada tek otorite hedeflenir.
            $dt = Carbon::parse($value, $tz);
        } catch (\Throwable) {
            return '';
        }

        try {
            return $dt->locale($loc)->translatedFormat($pattern);
        } catch (\Throwable) {
            try {
                return $dt->format('Y-m-d H:i');
            } catch (\Throwable) {
                return '';
            }
        }
    }

    /**
     * Datetime (Carbon instance) -> human readable localized string.
     *
     * Kontrat:
     * - Input: Carbon/CarbonInterface (string parse yok)
     * - Blade içinde ->format(...) dağınıklığı yerine tek otorite
     */
    public static function humanDateTime(?CarbonInterface $dt, ?string $locale = null, string $pattern = 'd F Y — H:i'): string
    {
        if (! $dt) {
            return '';
        }

        $loc = self::resolveLocale($locale);

        try {
            return $dt->locale($loc)->translatedFormat($pattern);
        } catch (\Throwable) {
            // translatedFormat patlarsa en güvenli fallback: standart format
            try {
                return $dt->format('Y-m-d H:i');
            } catch (\Throwable) {
                return '';
            }
        }
    }

    public static function humanDateTimeShort(?CarbonInterface $dt, ?string $locale = null): string
    {
        return self::humanDateTime($dt, $locale, 'd.m.Y H:i');
    }

    private static function resolveLocale(?string $locale): string
    {
        $locale = is_string($locale) ? trim($locale) : '';

        return $locale !== '' ? $locale : app()->getLocale();
    }
}
