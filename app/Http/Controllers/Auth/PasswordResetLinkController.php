<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

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

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
