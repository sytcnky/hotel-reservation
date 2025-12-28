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
        // 1) user.locale
        $locale = null;

        if ($user = $request->user()) {
            $locale = is_string($user->locale) ? $user->locale : null;
        }

        // 2) fallback: config('app.locale')
        if (! is_string($locale) || trim($locale) === '') {
            $locale = (string) config('app.locale', 'tr');
        }

        $locale = strtolower(trim((string) $locale));

        // Admin panel guard: TR/EN dışına çıkma.
        if (! in_array($locale, self::ALLOWED, true)) {
            $locale = 'tr';
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
