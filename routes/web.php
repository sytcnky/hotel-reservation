<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Support\UIconRegistry;
use App\Support\Helpers\LocaleHelper;
use App\Support\Routing\LocalizedRoute;
use App\Support\Helpers\CurrencyHelper;

use App\Http\Controllers\TransferController;

use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\SettingsController;
use App\Http\Controllers\Account\CouponsController;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;

use App\Http\Controllers\TourController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\VillaController;

/** Hotel booking -> sepete ekleme */
LocalizedRoute::post('hotel.book', 'hotel/book', [CheckoutController::class, 'bookHotel']);

/** Transfer booking -> sepete ekleme */
LocalizedRoute::post('transfer.book', 'transfer/book', [CheckoutController::class, 'bookTransfer']);

/** Excursion (tour) booking -> sepete ekleme */
LocalizedRoute::post('tour.book', 'excursions/book', [CheckoutController::class, 'bookTour']);

/** Villa booking -> sepete ekleme */
LocalizedRoute::post('villa.book', 'villas/book', [CheckoutController::class, 'bookVilla']);

/** Checkout complete (sepet -> sipariş) */
LocalizedRoute::post(
    'checkout.complete',
    'checkout/complete',
    [CheckoutController::class, 'complete']
);

/** Sipariş teşekkür sayfası */
LocalizedRoute::get(
    'order.thankyou',
    'order/thankyou/{code}',
    function ($code) {
        return view('pages.order.thankyou', ['code' => $code]);
    }
);


/*
|--------------------------------------------------------------------------
| Locale switch
|--------------------------------------------------------------------------
*/

Route::get('/locale/{locale}', function (Request $request, string $locale) {
    $active  = LocaleHelper::active();

    if (in_array($locale, $active, true)) {
        if (auth()->check()) {
            auth()->user()->forceFill(['locale' => $locale])->save();
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);
    }

    $redirect = $request->query('redirect');

    if (is_string($redirect) && $redirect !== '') {
        return redirect($redirect);
    }

    return back();
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
LocalizedRoute::view('home', '', 'pages.home');

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

/** Ödeme */
LocalizedRoute::get('payment', 'payment/{code}', [PaymentController::class, 'show']);

// Başarı
LocalizedRoute::view('success', 'success', 'pages.payment.success');

/** Statik sayfalar */
LocalizedRoute::view('contact', 'contact', 'pages.contact.index');
LocalizedRoute::view('help', 'help', 'pages.help.index');
LocalizedRoute::get('cart', 'cart', [CartController::class, 'index']);
Route::delete('/cart/item/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/coupon/apply', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/cart/coupon/remove', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');


/** Guides list */
LocalizedRoute::get('guides', 'gezi-rehberi', function () {
    $guides = [
        [
            'title'        => 'Marmaris',
            'slug'         => 'marmaris-gezi-rehberi',
            'excerpt'      => 'Marmaris’te gezilecek yerler...',
            'cover'        => '/images/samples/popular-marmaris.jpg',
            'category'     => 'Ege',
            'published_at' => '2025-08-01',
        ],
        [
            'title'        => 'İçmeler',
            'slug'         => 'icmeler-gezi-rehberi',
            'excerpt'      => 'İçmeler’in en popüler plajları...',
            'cover'        => '/images/samples/icmeler-1.jpg',
            'category'     => 'Ege',
            'published_at' => '2025-07-20',
        ],
    ];

    return view('pages.guides.index', compact('guides'));
});

/** Guide detail */
LocalizedRoute::get('guides.show', 'gezi-rehberi/{slug}', function ($slug) {
    $all = collect([
        'sedir-adasi' => [
            'title' => 'Sedir Adası',
            'cover' => '/images/samples/popular-marmaris.jpg',
            'intro' => 'Altın renkli kumları, berrak koyları ve antik dokusuyla Sedir Adası...',
        ],
        'marmaris-gezi-rehberi' => [
            'title' => 'Marmaris Gezi Rehberi',
            'cover' => '/images/samples/popular-marmaris.jpg',
            'intro' => 'Marmaris’te gezilecek yerler, plajlar ve ipuçları.',
        ],
        'icmeler-gezi-rehberi' => [
            'title' => 'İçmeler Gezi Rehberi',
            'cover' => '/images/samples/icmeler-1.jpg',
            'intro' => 'İçmeler’in plajları, sakin koyları ve öneriler.',
        ],
    ]);

    abort_unless($all->has($slug), 404);

    $guide = $all[$slug];

    // Ek veriler
    $hotelsPath     = public_path('data/hotels.json');
    $villasPath     = public_path('data/villas/villas.json');
    $excursionsPath = public_path('data/excursions/excursions.json');

    $hotels     = File::exists($hotelsPath) ? json_decode(File::get($hotelsPath)) : [];
    $villas     = File::exists($villasPath) ? json_decode(File::get($villasPath), true) : [];
    $excursions = File::exists($excursionsPath) ? json_decode(File::get($excursionsPath), true) : [];

    $hotel             = $hotels[0] ?? null;
    $villa             = $villas[0] ?? null;
    $excursionsSidebar = array_slice($excursions, 0, 2);

    return view('pages.guides.show', compact(
        'guide',
        'slug',
        'hotel',
        'villa',
        'excursionsSidebar'
    ));
});


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

    // View sayfalar
    LocalizedRoute::view('account.dashboard', 'account/dashboard', 'pages.account.index');

    LocalizedRoute::view('account.bookings', 'account/bookings', 'pages.account.bookings');

    LocalizedRoute::get(
        'account.coupons',
        'account/coupons',      // fallback path
        [CouponsController::class, 'index']   // action
    );

    LocalizedRoute::view('account.tickets', 'account/tickets', 'pages.account.tickets');

    LocalizedRoute::view('account.settings', 'account/settings', 'pages.account.settings');

    LocalizedRoute::get('account.tickets.show', 'account/tickets/{id}', function ($id) {
        return view('pages.account.ticket-detail', ['id' => $id]);
    });

    // Form action'ları (PUT) - her aktif locale için
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
