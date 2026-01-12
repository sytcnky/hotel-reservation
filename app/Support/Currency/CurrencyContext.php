<?php

namespace App\Support\Currency;

use App\Models\Currency;
use App\Models\Language;
use App\Models\Setting;
use Illuminate\Http\Request;

class CurrencyContext
{
    /**
     * Çözüm sırası:
     * 1) auth()->user()->currency (aktifse)
     * 2) session('currency') (aktifse)
     * 3) locale -> Language.currency_id -> Currency.code (aktifse)
     * 4) settings.default_currency (aktifse)
     * 5) null
     */
    public static function code(?Request $request = null): ?string
    {
        $request ??= request();

        // 1) User currency
        if (auth()->check()) {
            $uc = (string) (auth()->user()->currency ?? '');
            $uc = strtoupper(trim($uc));
            if ($uc !== '' && self::isActiveCode($uc)) {
                return $uc;
            }
        }

        // 2) Session currency
        $sc = (string) ($request->session()->get('currency') ?? '');
        $sc = strtoupper(trim($sc));
        if ($sc !== '' && self::isActiveCode($sc)) {
            return $sc;
        }

        // 3) Locale -> language currency
        $lc = self::codeFromLocale($request->getLocale());
        if ($lc !== null) {
            return $lc;
        }

        // 4) Settings default_currency
        $dc = (string) Setting::get('default_currency', '');
        $dc = strtoupper(trim($dc));
        if ($dc !== '' && self::isActiveCode($dc)) {
            return $dc;
        }

        // 5) no currency
        return null;
    }

    public static function model(?Request $request = null): ?Currency
    {
        $code = self::code($request);
        if ($code === null) {
            return null;
        }

        return Currency::query()
            ->where('is_active', true)
            ->where('code', $code)
            ->first();
    }

    /**
     * Header dropdown vb. için seçenekler (UI listesi).
     * sort_order küçük önce.
     */
    public static function options(): array
    {
        return Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get(['code', 'symbol', 'name'])
            ->map(function (Currency $c) {
                return [
                    'code'   => $c->code,
                    'symbol' => $c->symbol,
                    'label'  => $c->name_l ?: ($c->code),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * currency.switch route'u burayı çağıracak.
     * - Session + User (varsa) set eder.
     */
    public static function set(string $code, ?Request $request = null): void
    {
        $request ??= request();

        $code = strtoupper(trim($code));
        if ($code === '' || ! self::isActiveCode($code)) {
            return;
        }

        if (auth()->check()) {
            auth()->user()
                ->forceFill(['currency' => $code])
                ->save();
        }

        $request->session()->put('currency', $code);
    }

    /**
     * Locale set edildikten sonra (ilk ziyaret) currency bootstrap.
     * - user veya session currency varsa dokunmaz.
     * - Language.currency_id üzerinden set eder (aktifse).
     * - Yoksa hiçbir şey yapmaz (resolver settings.default_currency ile çözer).
     */
    public static function bootstrapFromLocale(string $localeCode, ?Request $request = null): void
    {
        $request ??= request();

        // user currency varsa dokunma
        if (auth()->check()) {
            $uc = (string) (auth()->user()->currency ?? '');
            $uc = strtoupper(trim($uc));
            if ($uc !== '' && self::isActiveCode($uc)) {
                return;
            }
        }

        // session currency varsa dokunma
        $sc = (string) ($request->session()->get('currency') ?? '');
        $sc = strtoupper(trim($sc));
        if ($sc !== '' && self::isActiveCode($sc)) {
            return;
        }

        $code = self::codeFromLocale($localeCode);
        if ($code !== null) {
            $request->session()->put('currency', $code);
        }
    }

    private static function codeFromLocale(?string $localeCode): ?string
    {
        $localeCode = strtolower(trim((string) $localeCode));
        if ($localeCode === '') {
            return null;
        }

        $lang = Language::query()
            ->where('is_active', true)
            ->where('code', $localeCode)
            ->with(['currency:id,code,is_active'])
            ->first();

        if (! $lang || ! $lang->currency) {
            return null;
        }

        $code = strtoupper(trim((string) $lang->currency->code));
        if ($code === '' || ! self::isActiveCode($code)) {
            return null;
        }

        return $code;
    }

    private static function isActiveCode(string $code): bool
    {
        return Currency::query()
            ->where('is_active', true)
            ->where('code', $code)
            ->exists();
    }
}
