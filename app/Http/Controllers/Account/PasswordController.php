<?php

declare(strict_types=1);

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password as PasswordRule;

final class PasswordController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required','current_password'],
            'password'         => ['required','confirmed', PasswordRule::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $newPassword = (string) $request->input('password'); // <-- String yap

        // Model cast 'hashed' şifreler
        $request->user()->update([
            'password' => $newPassword,
        ]);

        // Diğer cihazlardaki oturumları sonlandır (yeni şifre ile)
        Auth::logoutOtherDevices($newPassword);

        // Mevcut oturumu tazele
        $request->session()->regenerate();

        return back()->with('status', 'password-updated');
    }
}
