<?php

namespace App\Http\Middleware;

use App\Support\Currency\CurrencyContext;
use App\Support\Helpers\LocaleHelper;
use Closure;
use Illuminate\Http\Request;

class SetLocaleFromRequest
{
    public function handle(Request $request, Closure $next)
    {
        $activeCodes = LocaleHelper::active();
        $defaultCode = LocaleHelper::defaultCode();

        // Bootstrap edge-case: no session
        if (! $request->hasSession()) {
            $code = LocaleHelper::codeFromBrowser($request) ?: $defaultCode;
            app()->setLocale($code);

            return $next($request);
        }

        $user = $request->user();

        // 1) Logged-in user preference (if active)
        if ($user && is_string($user->locale) && $user->locale !== '' && in_array($user->locale, $activeCodes, true)) {
            $code = $user->locale;
        }
        // 2) Session value (if active)
        elseif ($sessionLocale = $request->session()->get('locale')) {
            $code = in_array($sessionLocale, $activeCodes, true) ? $sessionLocale : $defaultCode;
        }
        // 3) First visit: browser match (if any), else default
        else {
            $code = LocaleHelper::codeFromBrowser($request) ?: $defaultCode;
        }

        $request->session()->put('locale', $code);
        app()->setLocale($code);

        // Locale belirlendikten sonra: ilk ziyaret currency bootstrap (overwrite yok).
        CurrencyContext::bootstrapFromLocale($code, $request);

        return $next($request);
    }
}
