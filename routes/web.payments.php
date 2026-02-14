<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Payments\Nestpay\NestpayCallbackController;
use App\Http\Controllers\Payments\Nestpay\NestpayReturnController;
use App\Http\Controllers\Payments\Nestpay\NestpayResultController;
use App\Http\Controllers\Payments\Nestpay\NestpayStatusController;
use App\Http\Middleware\SetLocaleFromRequest;
use App\Support\Routing\LocalizedRoute;
use Illuminate\Support\Facades\Route;

/**
 * -------------------------
 * ICR - Payment Routes
 * -------------------------
 * Kontrat:
 * - finalize yalnız callback ile
 * - ok/fail yalnız UI
 * - callback localized değildir, CSRF hariç olmalıdır
 */

/** Ödeme sayfası (görüntüleme + işleme) */
foreach (LocalizedRoute::get('payment', [PaymentController::class, 'show']) as $route) {
    $route->middleware('throttle:60,1');
}

/** Sepetten ödeme başlangıcı (ÜYE kullanıcı) */
foreach (LocalizedRoute::post('checkout.start', [PaymentController::class, 'start']) as $route) {
    $route->middleware('throttle:10,1');
}

/** Login sayfasındaki misafir formu → ödeme başlangıcı (MİSAFİR) */
Route::post('/checkout/guest', [PaymentController::class, 'startGuest'])
    ->name('checkout.start.guest')
    ->middleware('throttle:5,1');

/** Başarılı Ödeme */
LocalizedRoute::view('success', 'pages.payment.success');

/**
 * -------------------------
 * Nestpay 3D Pay Hosting (P0)
 * -------------------------
 */

/**
 * Result page (UI only, localized)
 * - ok/fail dönüşleri burada birleşir
 * - sayfa status poll yapar (callback sonucu ile aynı sayfada güncellenir)
 */
foreach (LocalizedRoute::get('payment.result', [NestpayResultController::class, 'show']) as $route) {
    $route->middleware('throttle:120,1');
}

/**
 * Status endpoint (browser poll)
 * - localized değil
 * - CSRF gerekmez (GET)
 * - Locale middleware'i karışmamalı
 */
Route::get('/payment/nestpay/status', [NestpayStatusController::class, 'show'])
    ->name('payment.nestpay.status')
    ->withoutMiddleware(SetLocaleFromRequest::class)
    ->middleware('throttle:300,1');

/**
 * Callback (server-to-server)
 * - localized değil
 * - CSRF hariç olmalı (bootstrap/app.php validateCsrfTokens except)
 * - finalize burada (idempotent)
 *
 * Not: Locale middleware'i callback'e karışmamalı.
 */
Route::post('/payment/nestpay/callback', [NestpayCallbackController::class, 'handle'])
    ->name('payment.nestpay.callback')
    ->withoutMiddleware(SetLocaleFromRequest::class)
    ->middleware('throttle:120,1');

/**
 * Return OK/FAIL (UI only)
 * - Banka GET veya POST ile dönebilir
 * - localized değil
 * - Locale middleware'i karışmamalı
 */
Route::match(['GET', 'POST'], '/payment/nestpay/return/ok', [NestpayReturnController::class, 'ok'])
    ->name('payment.nestpay.return.ok')
    ->withoutMiddleware(SetLocaleFromRequest::class)
    ->middleware('throttle:120,1');

Route::match(['GET', 'POST'], '/payment/nestpay/return/fail', [NestpayReturnController::class, 'fail'])
    ->name('payment.nestpay.return.fail')
    ->withoutMiddleware(SetLocaleFromRequest::class)
    ->middleware('throttle:120,1');
