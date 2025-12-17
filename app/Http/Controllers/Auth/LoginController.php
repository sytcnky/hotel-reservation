<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class LoginController extends Controller
{
    // GET /login
    public function create()
    {
        return view('pages.auth.login');
    }

    // POST /login
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        // Önce redirect parametresine bak (örn. /cart, /checkout vs.)
        $redirect = $request->input('redirect');

        if (is_string($redirect) && $redirect !== '') {
            // Basit güvenlik: sadece relative URL veya aynı domain’e izin ver
            if (str_starts_with($redirect, '/') || str_starts_with($redirect, url('/'))) {
                return redirect()->to($redirect);
            }
        }

        // Aksi halde eski davranış: intended → yoksa home
        return redirect()->intended(localized_route('home'));
    }

    // POST /logout
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // logout yönlendirmesi de locale-aware
        return redirect(localized_route('home'));
    }
}
