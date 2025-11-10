<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

final class NewPasswordController extends Controller
{
    /**
     * GET /reset-password/{token}
     * Token ile yeni şifre ekranı
     */
    public function create(Request $request, string $token)
    {
        return view('pages.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /**
     * POST /reset-password
     * Yeni şifreyi kaydet
     */
    public function store(Request $request)
    {
        $request->validate([
            'token'                 => ['required'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                // User modelindeki 'password' cast'i hashed olduğu için direkt atıyoruz
                $user->password = $password;
                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
