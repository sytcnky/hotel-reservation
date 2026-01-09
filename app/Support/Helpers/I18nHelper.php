<?php

namespace App\Support\Helpers;

class I18nHelper
{
    /**
     * Locale-keyed (örn: ['tr' => '...', 'en' => '...']) bir değerden string seçer.
     *
     * Kontrat:
     * - "first value" fallback YOK.
     * - Hard tr/en YOK.
     * - Sadece: data[ui] ?? data[base] ?? ''.
     * - Scalar/string değilse null döner.
     */
    public static function scalar(mixed $value, string $uiLocale, string $baseLocale): ?string
    {
        if (! is_array($value)) {
            return $value !== null ? (string) $value : null;
        }

        $picked = $value[$uiLocale] ?? $value[$baseLocale] ?? '';

        if (is_string($picked)) {
            return $picked;
        }

        if (is_scalar($picked)) {
            return (string) $picked;
        }

        return null;
    }

    /**
     * Repeater/list tarzı alanları (örn: [{value:"..."}] veya ["..."]) düz string listeye çevirir.
     *
     * Beklenen input formatları:
     * - ['tr' => [..], 'en' => [..]]   (locale-keyed)
     * - [..]                          (doğrudan liste)
     *
     * Kontrat:
     * - "first value" fallback YOK.
     * - Hard tr/en YOK.
     * - Locale-keyed ise: data[ui] ?? data[base] ?? [].
     * - Trim + boşları filtreler.
     */
    public static function stringList(mixed $value, string $uiLocale, string $baseLocale): array
    {
        if (! is_array($value)) {
            return [];
        }

        // Locale-keyed ise seç, değilse direkt liste kabul et.
        $list = array_key_exists($uiLocale, $value) || array_key_exists($baseLocale, $value)
            ? ($value[$uiLocale] ?? $value[$baseLocale] ?? [])
            : $value;

        if (! is_array($list)) {
            return [];
        }

        return collect($list)
            ->map(function ($item) {
                $v = is_array($item) ? ($item['value'] ?? null) : $item;
                return is_string($v) ? trim($v) : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Locale-keyed map'ten ham değer seçer (string olmak zorunda değil).
     *
     * Kontrat:
     * - "first value" fallback YOK.
     * - Sadece: map[ui] ?? map[base] ?? null.
     */
    public static function pick(mixed $map, string $uiLocale, string $baseLocale): mixed
    {
        if (! is_array($map)) {
            return null;
        }

        return $map[$uiLocale] ?? $map[$baseLocale] ?? null;
    }
}
