<?php

namespace App\Support\Helpers;

use App\Models\Language;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LocaleHelper
{
    /**
     * Active language codes (canonical codes used across the app)
     * e.g. ['tr', 'en']
     */
    public static function active(): array
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->all();
    }

    /**
     * Active languages map: code => locale
     * e.g. ['tr' => 'tr_TR', 'en' => 'en_GB']
     */
    public static function activeLocaleMap(): array
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('locale', 'code')
            ->toArray();
    }

    /**
     * System default locale code.
     *
     * Contract:
     * - Primary source: settings.default_locale (must be one of active languages)
     * - If misconfigured: fallback to active[0]
     * - If no active languages exist (bootstrap edge case): fallback to config('app.locale')
     *
     * Returns canonical code (e.g. 'tr', 'en').
     */
    public static function defaultCode(): string
    {
        $activeCodes = static::active();

        // Bootstrap edge-case: no active languages yet (seed not done).
        if (empty($activeCodes)) {
            $cfg = (string) config('app.locale'); // config/app.php already defines a value
            return $cfg !== '' ? $cfg : 'en';
        }

        $candidate = Setting::get('default_locale');

        if (is_string($candidate)) {
            $candidate = trim($candidate);
            if ($candidate !== '' && in_array($candidate, $activeCodes, true)) {
                return $candidate;
            }
        }

        // If settings value is missing or not active, fallback to first active.
        return $activeCodes[0];
    }

    /**
     * Normalize / validate a canonical locale code.
     *
     * Rules:
     * - Accepts canonical codes only (e.g. 'tr', 'en').
     * - If candidate is empty or not active, returns defaultCode().
     * - defaultCode() itself already implements "last resort" behavior.
     */
    public static function normalizeCode(?string $candidate): string
    {
        $candidate = strtolower(trim((string) $candidate));

        if ($candidate === '') {
            return static::defaultCode();
        }

        $activeCodes = static::active();

        // If active languages not seeded yet, defaultCode() will fallback to config/app.locale.
        if (empty($activeCodes)) {
            return static::defaultCode();
        }

        if (in_array($candidate, $activeCodes, true)) {
            return $candidate;
        }

        return static::defaultCode();
    }

    /**
     * Resolve canonical language code from request's Accept-Language header.
     *
     * Matching order:
     * 1) Exact match against Language.locale (normalized) e.g. tr_TR / tr-TR
     * 2) Base match against Language.code e.g. tr
     *
     * Returns canonical code (e.g. 'tr') or null if no match.
     */
    public static function codeFromBrowser(Request $request): ?string
    {
        $activeMap = static::activeLocaleMap(); // code => locale
        if (empty($activeMap)) {
            return null; // bootstrap edge-case; caller will fallback to defaultCode()
        }

        $header = $request->header('Accept-Language');
        if (! is_string($header) || trim($header) === '') {
            return null;
        }

        $candidates = static::parseAcceptLanguage($header);
        if (empty($candidates)) {
            return null;
        }

        // Prepare normalized locale lookup: normalizedLocale => code
        $normalizedLocaleToCode = [];
        foreach ($activeMap as $code => $locale) {
            $norm = static::normalizeLocale((string) $locale); // e.g. tr_tr
            if ($norm !== '') {
                $normalizedLocaleToCode[$norm] = (string) $code;
            }
        }

        // 1) Exact match by locale
        foreach ($candidates as $cand) {
            $norm = static::normalizeLocale($cand); // e.g. tr_tr
            if ($norm !== '' && isset($normalizedLocaleToCode[$norm])) {
                return $normalizedLocaleToCode[$norm];
            }
        }

        // 2) Base match by code (first part)
        $activeCodes = array_keys($activeMap);
        foreach ($candidates as $cand) {
            $base = static::baseCode($cand); // e.g. tr
            if ($base !== '' && in_array($base, $activeCodes, true)) {
                return $base;
            }
        }

        return null;
    }

    /**
     * Public options for UI (unchanged behavior).
     */
    public static function options(): array
    {
        return Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['code', 'native_name', 'flag'])
            ->map(function (Language $lang) {
                $flag = $lang->flag;

                if ($flag) {
                    // FileUpload public/flags içine yazıyor:
                    // DB tipik değer: "flags/xxxx.svg"
                    if (
                        ! str_starts_with($flag, 'http://')
                        && ! str_starts_with($flag, 'https://')
                        && ! str_starts_with($flag, '/')
                    ) {
                        $flag = Storage::disk('public')->url($flag); // => /storage/flags/xxxx.svg
                    }
                }

                return [
                    'code'  => $lang->code,
                    'label' => $lang->native_name ?: $lang->code,
                    'flag'  => $flag,
                ];
            })
            ->keyBy('code')
            ->toArray();
    }

    /**
     * Parse Accept-Language header and return ordered list of language tags (no q needed, already sorted).
     */
    private static function parseAcceptLanguage(string $header): array
    {
        $parts = array_filter(array_map('trim', explode(',', $header)));

        $items = [];
        foreach ($parts as $part) {
            // examples: "tr-TR;q=0.9" | "en-US" | "en;q=0.7"
            $segments = array_map('trim', explode(';', $part));
            $tag = $segments[0] ?? '';
            if ($tag === '') {
                continue;
            }

            $q = 1.0;
            if (isset($segments[1]) && str_starts_with($segments[1], 'q=')) {
                $qVal = substr($segments[1], 2);
                if (is_numeric($qVal)) {
                    $q = (float) $qVal;
                }
            }

            $items[] = ['tag' => $tag, 'q' => $q];
        }

        usort($items, fn ($a, $b) => $b['q'] <=> $a['q']);

        return array_values(array_unique(array_map(fn ($x) => (string) $x['tag'], $items)));
    }

    /**
     * Normalize locale tag for comparison:
     * - lowercases
     * - replaces '-' with '_'
     */
    private static function normalizeLocale(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = str_replace('-', '_', $value);

        return mb_strtolower($value);
    }

    /**
     * Get base language code from tag: "tr-TR" or "tr_TR" => "tr"
     */
    private static function baseCode(string $tag): string
    {
        $tag = trim($tag);
        if ($tag === '') {
            return '';
        }

        $tag = str_replace('-', '_', $tag);
        $parts = explode('_', $tag);

        return mb_strtolower(trim($parts[0] ?? ''));
    }
}
