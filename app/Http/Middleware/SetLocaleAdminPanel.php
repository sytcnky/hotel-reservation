<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleAdminPanel
{
    /**
     * Admin panel sadece TR/EN (resources/lang/{tr,en}/admin.php) üzerinden çalışıyor.
     */
    private const ALLOWED = ['tr', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        // Tek otorite: session('panel_locale')
        $locale = (string) session('panel_locale', 'en');
        $locale = strtolower(trim($locale));

        // Guard: sadece tr / en
        if (! in_array($locale, self::ALLOWED, true)) {
            $locale = 'en';
        }

        App::setLocale($locale);

        try {
            Carbon::setLocale($locale);
        } catch (\Throwable) {
            // sessiz geç
        }

        return $next($request);
    }

}
