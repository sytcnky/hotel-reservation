<?php

namespace App\Support\Helpers;

use App\Models\Language;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LocaleHelper
{
    /**
     * Aktif dil kodları (uygulama genelinde kullanılan kanonik kodlar)
     * örn: ['tr', 'en']
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
     * Aktif diller haritası: code => locale
     * örn: ['tr' => 'tr_TR', 'en' => 'en_GB']
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
     * Sistem varsayılan locale kodu.
     *
     * Kontrat:
     * - Birincil kaynak: settings.default_locale (aktif dillerden biri olmalı)
     * - Hatalı/eksik ise: aktif[0] (ilk aktif dil) döner
     * - Bootstrap edge-case: hiç aktif dil yoksa (seed yapılmadıysa) tek fallback: 'en'
     *
     * Kanonik kod döner (örn: 'tr', 'en').
     */
    public static function defaultCode(): string
    {
        $activeCodes = static::active();

        // Bootstrap edge-case: henüz aktif dil yoksa tek fallback 'en'
        if (empty($activeCodes)) {
            return 'en';
        }

        $candidate = Setting::get('default_locale');

        if (is_string($candidate)) {
            $candidate = trim($candidate);
            if ($candidate !== '' && in_array($candidate, $activeCodes, true)) {
                return $candidate;
            }
        }

        // Settings değeri yoksa veya aktif değilse, ilk aktif dile düş.
        return $activeCodes[0];
    }

    /**
     * Kanonik locale kodunu normalize / validate eder.
     *
     * Kurallar:
     * - Sadece kanonik kodları kabul eder (örn: 'tr', 'en').
     * - Candidate boşsa veya aktif değilse defaultCode() döner.
     * - defaultCode() kendi içinde "son çare" davranışını uygular.
     */
    public static function normalizeCode(?string $candidate): string
    {
        $candidate = strtolower(trim((string) $candidate));

        if ($candidate === '') {
            return static::defaultCode();
        }

        $activeCodes = static::active();

        // Bootstrap edge-case: aktif dil yoksa defaultCode() zaten 'en' döner.
        if (empty($activeCodes)) {
            return static::defaultCode();
        }

        if (in_array($candidate, $activeCodes, true)) {
            return $candidate;
        }

        return static::defaultCode();
    }

    /**
     * Request'in Accept-Language header'ından kanonik dil kodunu çözer.
     *
     * Eşleşme sırası:
     * 1) Language.locale alanına exact match (normalize edilmiş) örn: tr_TR / tr-TR
     * 2) Language.code alanına base match örn: tr
     *
     * Kanonik code (örn: 'tr') döner veya eşleşme yoksa null.
     */
    public static function codeFromBrowser(Request $request): ?string
    {
        $activeMap = static::activeLocaleMap(); // code => locale
        if (empty($activeMap)) {
            return null; // bootstrap edge-case; caller defaultCode() ile devam eder
        }

        $header = $request->header('Accept-Language');
        if (! is_string($header) || trim($header) === '') {
            return null;
        }

        $candidates = static::parseAcceptLanguage($header);
        if (empty($candidates)) {
            return null;
        }

        // Normalize edilmiş locale lookup: normalizedLocale => code
        $normalizedLocaleToCode = [];
        foreach ($activeMap as $code => $locale) {
            $norm = static::normalizeLocale((string) $locale); // örn: tr_tr
            if ($norm !== '') {
                $normalizedLocaleToCode[$norm] = (string) $code;
            }
        }

        // 1) Locale ile exact match
        foreach ($candidates as $cand) {
            $norm = static::normalizeLocale($cand); // örn: tr_tr
            if ($norm !== '' && isset($normalizedLocaleToCode[$norm])) {
                return $normalizedLocaleToCode[$norm];
            }
        }

        // 2) Code ile base match (ilk parça)
        $activeCodes = array_keys($activeMap);
        foreach ($candidates as $cand) {
            $base = static::baseCode($cand); // örn: tr
            if ($base !== '' && in_array($base, $activeCodes, true)) {
                return $base;
            }
        }

        return null;
    }

    /**
     * UI için public seçenekler
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
                    // FileUpload public/flags içine yazar:
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
     * Accept-Language header'ını parse eder ve sıralı dil tag listesi döner
     * (q değerine göre sıralanır, tekrarlar ayıklanır).
     */
    private static function parseAcceptLanguage(string $header): array
    {
        $parts = array_filter(array_map('trim', explode(',', $header)));

        $items = [];
        foreach ($parts as $part) {
            // örnekler: "tr-TR;q=0.9" | "en-US" | "en;q=0.7"
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
     * Karşılaştırma için locale tag normalize eder:
     * - lowercase
     * - '-' karakterlerini '_' yapar
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
     * Tag'den base language code alır: "tr-TR" veya "tr_TR" => "tr"
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
