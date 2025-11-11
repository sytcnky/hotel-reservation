<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Support\Helpers\LocaleHelper;
use Closure;
use Illuminate\Http\Request;

class SetLocaleFromRequest
{
    public function handle(Request $request, Closure $next)
    {
        $active = LocaleHelper::active();
        if (empty($active)) {
            $active = [config('app.locale', 'tr')];
        }

        $default = Setting::get('default_locale', config('app.locale', 'tr'));
        if (! in_array($default, $active, true)) {
            $default = $active[0];
        }

        // Session yoksa: sadece default locale set et, session kullanma
        if (! $request->hasSession()) {
            app()->setLocale($default);
            return $next($request);
        }

        $user = $request->user();

        if ($user && $user->locale && in_array($user->locale, $active, true)) {
            $locale = $user->locale;
        } elseif ($sessionLocale = $request->session()->get('locale')) {
            $locale = in_array($sessionLocale, $active, true) ? $sessionLocale : $default;
        } else {
            $locale = $default;
        }

        $request->session()->put('locale', $locale);
        app()->setLocale($locale);

        return $next($request);
    }
}
