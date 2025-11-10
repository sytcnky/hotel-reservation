<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = 'tr';

        if ($user = $request->user()) {
            $userLocale = strtolower((string) $user->locale);
            if (in_array($userLocale, ['tr', 'en'], true)) {
                $locale = $userLocale;
            }
        }

        App::setLocale($locale);

        try {
            \Carbon\Carbon::setLocale($locale);
        } catch (\Throwable $e) {
            // sessiz geç
        }

        return $next($request);
    }
}
