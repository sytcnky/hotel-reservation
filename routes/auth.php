<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

/*
|--------------------------------------------------------------------------
| Guest Routes (Sadece giriş yapmamış kullanıcı)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [LoginController::class, 'create'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('login.store');

    // Register
    Route::get('/register', [RegisterController::class, 'create'])
        ->name('register');

    Route::post('/register', [RegisterController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('register.store');

    // Forgot password
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Reset password
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Logout (Sadece giriş yapmış kullanıcı)
|--------------------------------------------------------------------------
*/

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Email Verification
|--------------------------------------------------------------------------
*/

// Doğrulama bilgi ekranı
Route::get('/email/verify', function () {
    return view('pages.auth.verify-email');
})
    ->middleware('auth')
    ->name('verification.notice');

// Link ile doğrulama
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect(localized_route('home'));
})
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

// Doğrulama mailini tekrar gönder
Route::post('/email/verification-notification', function (Request $request) {

    $user = $request->user();

    // Kullanıcı + IP bazlı key
    $key = 'verify-email:' . $user->id . ':' . $request->ip();

    // 5 dakika içinde max 5 deneme
    if (RateLimiter::tooManyAttempts($key, 5)) {
        $seconds = RateLimiter::availableIn($key);

        return back()
            ->withErrors([
                'verify' => "Çok fazla deneme yaptınız. Lütfen tekrar deneyin.",
            ])
            ->with('verify_retry', $seconds);
    }

    RateLimiter::hit($key, 300); // 300 sn = 5 dk

    if ($user->hasVerifiedEmail()) {
        return redirect(localized_route('home'));
    }

    $user->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent');

})
    ->middleware('auth')
    ->name('verification.send');

