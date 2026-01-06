<?php

use App\Http\Controllers\HomeController;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Support\UIconRegistry;
use App\Support\Helpers\LocaleHelper;
use App\Support\Routing\LocalizedRoute;
use App\Support\Helpers\CurrencyHelper;

use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\SettingsController;
use App\Http\Controllers\Account\CouponsController;
use App\Http\Controllers\Account\BookingsController;
use App\Http\Controllers\Account\SupportTicketsController;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;

use App\Http\Controllers\TourController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\VillaController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\TravelGuideController;

/** Mail Test */
use App\Models\Order;
use App\Models\RefundAttempt;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\ResetPasswordNotification;

/** Hotel booking -> sepete ekleme */
LocalizedRoute::post('hotel.book', 'hotel/book', [CheckoutController::class, 'bookHotel']);

/** Transfer booking -> sepete ekleme */
LocalizedRoute::post('transfer.book', 'transfer/book', [CheckoutController::class, 'bookTransfer']);

/** Excursion (tour) booking -> sepete ekleme */
LocalizedRoute::post('tour.book', 'excursions/book', [CheckoutController::class, 'bookTour']);

/** Villa booking -> sepete ekleme */
LocalizedRoute::post('villa.book', 'villas/book', [CheckoutController::class, 'bookVilla']);

/*
|--------------------------------------------------------------------------
| Locale switch
|--------------------------------------------------------------------------
*/

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    $active = LocaleHelper::active();

    if (in_array($locale, $active, true)) {
        if (auth()->check()) {
            auth()->user()->forceFill(['locale' => $locale])->save();
        }

        if ($request->hasSession()) {
            session(['locale' => $locale]);
        }

        app()->setLocale($locale);
    }

    $redirect = $request->query('redirect');

    // Prevent open redirect: allow only same-app relative paths.
    if (is_string($redirect)) {
        $redirect = trim($redirect);

        if ($redirect !== '' && str_starts_with($redirect, '/')) {
            return redirect($redirect);
        }
    }

    // Safe fallback
    try {
        return redirect(\localized_route('home'));
    } catch (\Throwable) {
        return redirect('/');
    }
})->name('locale.switch');


/*
|--------------------------------------------------------------------------
| Currency switch
|--------------------------------------------------------------------------
*/
Route::get('/currency/{code}', function (string $code) {
    if (CurrencyHelper::exists($code)) {

        if (auth()->check()) {
            auth()->user()
                ->forceFill(['currency' => $code])
                ->save();
        }

        session(['currency' => $code]);
    }

    return back();
})->name('currency.switch');


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Root redirect -> /{locale}
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $active = LocaleHelper::active();
    $default = config('app.locale', 'tr');

    $locale =
        session('locale')
        ?? (auth()->user()->locale ?? $default);

    if (! in_array($locale, $active, true)) {
        $locale = $active[0] ?? $default;
    }

    return redirect('/' . $locale);
});

/*
|--------------------------------------------------------------------------
| Site Routes (FE) - localized
|--------------------------------------------------------------------------
|
| Her route:
|  - URL: /{locale}/{slug}
|  - İsim: {locale}.{baseName}
|  - Blade: localized_route('baseName')
|
*/

/** Home */
LocalizedRoute::get('home', '', [HomeController::class, 'index']);

/** Hotels list */
LocalizedRoute::get('hotels', 'oteller', [HotelController::class, 'index']);

/** Hotel detail */
LocalizedRoute::get('hotel.detail', 'hotel/{slug}', [HotelController::class, 'show']);

/** Transfers */
LocalizedRoute::get('transfers', 'transfers', [TransferController::class, 'index']);

/** Villas */
LocalizedRoute::get('villa', 'villalar', [VillaController::class, 'index']);

/** Villa detail */
LocalizedRoute::get('villa.villa-detail', 'villa/{slug}', [VillaController::class, 'show']);

/** Excursions */
LocalizedRoute::get('excursions', 'excursions', [TourController::class, 'index']);

/** Excursion detail */
LocalizedRoute::get('excursions.detail', 'excursions/{slug}', [TourController::class, 'show']);

/** Guide list */
LocalizedRoute::get('guides', 'guides', [TravelGuideController::class, 'index']);

/** Guide detail */
LocalizedRoute::get('guides.show', 'guides/{slug}', [TravelGuideController::class, 'show']);

/** Ödeme sayfası (görüntüleme + işleme) */
LocalizedRoute::get('payment', 'payment/{code}', [PaymentController::class, 'show']);

LocalizedRoute::post('payment.process', 'payment/{code}', [PaymentController::class, 'process']);

/** Sepetten ödeme başlangıcı (ÜYE kullanıcı) */
LocalizedRoute::post('checkout.start', 'checkout/start', [PaymentController::class, 'start']);

/** Login sayfasındaki misafir formu → ödeme başlangıcı (MİSAFİR) */
Route::post('/checkout/guest', [PaymentController::class, 'startGuest'])->name('checkout.start.guest');

/** 3D Secure demo ekranı */
LocalizedRoute::get('payment.3ds', 'payment/{code}/3ds', [PaymentController::class, 'show3ds']);
LocalizedRoute::post('payment.3ds.complete', 'payment/{code}/3ds/complete', [PaymentController::class, 'complete3ds']);

/** Başarılı Ödeme */
LocalizedRoute::view('success', 'success', 'pages.payment.success');

/** Sepet */
LocalizedRoute::get('cart', 'cart', [CartController::class, 'index']);
Route::delete('/cart/item/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/coupon/apply', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/cart/coupon/remove', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

/** Statik sayfalar */
LocalizedRoute::view('contact', 'contact', 'pages.contact.index');
LocalizedRoute::view('help', 'help', 'pages.help.index');

/** Legal sayfalar */

LocalizedRoute::view('privacy_policy', 'privacy-policy', 'pages.legal.show', ['pageKey' => 'privacy_policy_page']);
LocalizedRoute::view('terms_of_use', 'terms-of-use', 'pages.legal.show', ['pageKey' => 'terms_of_use_page']);
LocalizedRoute::view('distance_sales', 'distance-sales', 'pages.legal.show', ['pageKey' => 'distance_sales_page']);


/*
|--------------------------------------------------------------------------
| Account alanı (localized)
|--------------------------------------------------------------------------
|
| - URL: /{locale}/{slug}
| - Base: account.*
| - İsim: {locale}.account.*
|
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // Hesabım - Dashboard
    LocalizedRoute::view('account.dashboard', 'account/dashboard', 'pages.account.index');

    // Hesabım - Rezervasyonlarım
    LocalizedRoute::get('account.bookings', 'account/bookings', [BookingsController::class, 'index']);

    // Hesabım - Kuponlar
    LocalizedRoute::get('account.coupons', 'account/coupons', [CouponsController::class, 'index']);

    // Hesabım - Destek Talepleri
    LocalizedRoute::get('account.tickets.create', 'account/tickets/create', [SupportTicketsController::class, 'create']);

    LocalizedRoute::get('account.tickets', 'account/tickets', [SupportTicketsController::class, 'index']);

    foreach (LocalizedRoute::get('account.tickets.show', 'account/tickets/{ticket}', [SupportTicketsController::class, 'show']) as $route) {
        $route->whereNumber('ticket');
    }

    LocalizedRoute::post('account.tickets.store', 'account/tickets', [SupportTicketsController::class, 'store']);

    foreach (LocalizedRoute::post('account.tickets.message', 'account/tickets/{ticket}/messages', [SupportTicketsController::class, 'storeMessage']) as $route) {
        $route->whereNumber('ticket');
    }

    // Hesabım - Ayalar
    LocalizedRoute::view('account.settings', 'account/settings', 'pages.account.settings');

    // Hesabım - Ayalar - Form action'ları (PUT) - her aktif locale için
    foreach (LocaleHelper::active() as $locale) {
        Route::prefix($locale)
            ->name($locale . '.')
            ->group(function () {
                Route::put('/account/settings', [SettingsController::class, 'update'])
                    ->name('account.settings.update');

                Route::put('/account/password', [PasswordController::class, 'update'])
                    ->name('account.password.update');
            });
    }
});

/*
|--------------------------------------------------------------------------
| Admin Utilities & Debug
|--------------------------------------------------------------------------
*/

Route::get('/admin/uicons', function (Request $r) {
    $variant = $r->string('variant', 'outline');
    $icons   = UIconRegistry::list($variant);

    return response()->json($icons, 200, [
        'Cache-Control' => 'public, max-age=86400',
        'ETag'          => md5($variant . '|' . count($icons)),
    ]);
})->middleware(['auth']);

if (app()->environment('local')) {
    Route::get('/whoami', fn () => [
        'id'    => auth()->id(),
        'email' => auth()->user()?->email,
        'roles' => auth()->user()?->getRoleNames(),
    ])->middleware('auth');
}


/*
|--------------------------------------------------------------------------
| Test
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    Route::get('/__preview/emails/order-approved/{order}', function (Order $order) {
        return view('emails.orders.customer-approved', [
            'order' => $order,
        ]);
    })->middleware('auth');
}

if (app()->environment('local')) {
    Route::get('/__preview/emails/order-created/{order}', function (Order $order) {
        return view('emails.orders.customer-created', [
            'order' => $order,
        ]);
    })->middleware('auth');
}

if (app()->environment('local')) {
    Route::get('/__preview/emails/order-cancelled/{order}', function (Order $order) {
        return view('emails.orders.customer-cancelled', [
            'order' => $order,
        ]);
    })->middleware('auth');
}

Route::get('/__preview/emails/order-refunded/{refund}', function (RefundAttempt $refund) {
    $refund->loadMissing('order');

    return view('emails.orders.customer-refunded', [
        'refund' => $refund,
        'order'  => $refund->order,
    ]);
})->middleware('auth');

if (app()->environment('local')) {
    Route::get('/__preview/emails/ops-created/{order}', function (Order $order) {
        return view('emails.orders.ops-created', [
            'order' => $order,
            'layoutVariant' => 'ops',
        ]);
    })->middleware('auth');
}

Route::get('/__test/mail/support-customer-replied/{ticket}', function (\App\Models\SupportTicket $ticket) {
    $message = $ticket->messages()->where('is_from_ops', true)->latest()->first();

    return new \App\Mail\SupportTicketAgentMessageCustomerMail($ticket, $message);
});

if (app()->environment('local')) {

    Route::get('/__preview/emails/support/ops-ticket-created/{ticket}', function (SupportTicket $ticket) {
        $message = $ticket->messages()->orderBy('id')->firstOrFail();

        return view('emails.support.ops-ticket-created', [
            'ticket' => $ticket,
            'supportMessage' => $message,
            'layoutVariant' => 'ops',
        ]);
    })->middleware('auth');

    Route::get('/__preview/emails/support/ops-customer-message/{ticket}/{message}', function (
        SupportTicket $ticket,
        SupportMessage $message
    ) {
        abort_unless((int) $message->support_ticket_id === (int) $ticket->id, 404);

        return view('emails.support.ops-customer-message', [
            'ticket' => $ticket,
            'supportMessage' => $message,
            'layoutVariant' => 'ops',
        ]);
    })->middleware('auth');

    Route::get('/__preview/emails/support/customer-agent-message/{ticket}/{message}', function (
        SupportTicket $ticket,
        SupportMessage $message
    ) {
        abort_unless((int) $message->support_ticket_id === (int) $ticket->id, 404);

        return view('emails.support.customer-agent-message', [
            'ticket' => $ticket,
            'supportMessage' => $message,
        ]);
    })->middleware('auth');
}

if (app()->environment('local')) {

    // 1) Verify Email preview
    Route::get('/__preview/emails/auth/verify', function (Request $request) {
        $user = $request->user();
        abort_unless($user, 403);

        $notification = new VerifyEmailNotification();

        // Notification -> MailMessage
        $mailMessage = $notification->toMail($user);

        // MailMessage -> Render (blade'in neyse onu basar)
        return $mailMessage->render();
    })->middleware('auth');

    // 2) Reset Password preview
    Route::get('/__preview/emails/auth/reset', function (Request $request) {
        $user = $request->user();
        abort_unless($user, 403);

        $token = 'TEST_TOKEN_123'; // preview token

        $notification = new ResetPasswordNotification($token);

        $mailMessage = $notification->toMail($user);

        return $mailMessage->render();
    })->middleware('auth');
}
