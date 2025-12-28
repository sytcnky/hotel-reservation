<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Helpers\LocaleHelper;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Spatie\Permission\Models\Role;

final class RegisterController extends Controller
{
    public function create()
    {
        return view('pages.auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'phone'      => ['required', 'regex:/^\+[1-9]\d{7,14}$/'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'   => ['required', 'confirmed', PasswordRule::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $locale = app()->getLocale() ?: LocaleHelper::defaultCode();

        $user = User::create([
            'first_name' => $request->string('first_name'),
            'last_name'  => $request->string('last_name'),
            'phone'      => $request->string('phone'),
            'email'      => $request->string('email'),
            'password'   => $request->string('password'),
            'locale'     => $locale,
        ]);

        // Yeni üyeler otomatik müşteri rolü alır
        Role::findOrCreate('customer', 'web');
        $user->syncRoles(['customer']);

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice')
            ->with('status', 'verification-link-sent');
    }
}
