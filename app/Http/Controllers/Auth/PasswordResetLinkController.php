<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

final class PasswordResetLinkController extends Controller
{
    /**
     * GET /forgot-password
     * Şifremi unuttum formu
     */
    public function create()
    {
        return view('pages.auth.forgot-password');
    }

    /**
     * POST /forgot-password
     * Reset linki gönder
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $ip    = (string) $request->ip();

        // Dakikada 5 istek (email+ip bazlı)
        $key = 'pw-reset-link:' . sha1($email . '|' . $ip);

        $maxAttempts  = 5;
        $decaySeconds = 300;

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($key);

            return back()
                ->withInput()
                ->with('pw_reset_retry', $seconds)
                ->withErrors([
                    'email' => "Çok fazla deneme yaptın. Lütfen {$seconds} saniye sonra tekrar dene.",
                ]);
        }

        \Illuminate\Support\Facades\RateLimiter::hit($key, $decaySeconds);

        // Güvenlik: kullanıcı var/yok bilgisi vermiyoruz.
        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'Şifre sıfırlama bağlantısı e-postana gönderildi.');
    }


}
