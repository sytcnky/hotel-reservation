<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Support\UIconRegistry;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\SettingsController;
use App\Support\Helpers\LocaleHelper;
use App\Support\Routing\LocalizedRoute;
use App\Http\Controllers\TransferController;
use App\Support\Helpers\CurrencyHelper;

/*
|--------------------------------------------------------------------------
| Locale switch
|--------------------------------------------------------------------------
*/

Route::get('/locale/{locale}', function (string $locale) {
    $active = LocaleHelper::active();

    if (in_array($locale, $active, true)) {
        if (auth()->check()) {
            auth()->user()->forceFill(['locale' => $locale])->save();
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);
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
LocalizedRoute::get('hotels', 'hotels', function () {
    $path = public_path('data/hotels.json');
    abort_unless(File::exists($path), 404);

    $hotels = collect(json_decode(File::get($path)));

    if (request()->filled('stars')) {
        $stars = (array) request()->input('stars');
        $hotels = $hotels->filter(fn($hotel) => in_array($hotel->stars, $stars));
    }

    if (request()->filled('category')) {
        $category = request()->input('category');
        $hotels = $hotels->filter(fn($hotel) => $hotel->category === $category);
    }

    return view('pages.hotel.index', compact('hotels'));
});

/** Hotel detail */
LocalizedRoute::get('hotel.detail', 'hotel/{id}', function ($id) {
    $path = public_path('data/hotels.json');
    abort_unless(File::exists($path), 404);

    $hotels = json_decode(File::get($path), true);
    $hotel  = collect($hotels)->firstWhere('id', (int) $id);
    abort_unless($hotel, 404);

    return view('pages.hotel.hotel-detail', compact('hotel'));
});

/** Transfers */
LocalizedRoute::get('transfers', 'transfers', [TransferController::class, 'index']);

/** Villas */
LocalizedRoute::get('villa', 'villalar', function () {
    $path = public_path('data/villas/villas.json');
    abort_unless(File::exists($path), 404);

    $villas = json_decode(File::get($path), true);
    return view('pages.villa.index', compact('villas'));
});

/** Villa detail */
LocalizedRoute::get('villa.villa-detail', 'villa/{slug}', function ($slug) {
    $path = public_path('data/villas/villas.json');
    abort_unless(File::exists($path), 404);

    $villas = json_decode(File::get($path), true);
    $villa  = collect($villas)->firstWhere('slug', $slug);
    abort_unless($villa, 404);

    return view('pages.villa.villa-detail', compact('villa'));
});

/** Excursions */
LocalizedRoute::get('excursions', 'excursions', function () {
    $path = public_path('data/excursions/excursions.json');
    abort_unless(File::exists($path), 404);

    $excursions = json_decode(File::get($path), true);
    return view('pages.excursion.index', compact('excursions'));
});

/** Excursion detail */
LocalizedRoute::get('excursions.detail', 'excursions/{slug}', function ($slug) {
    $path = public_path('data/excursions/excursions.json');
    abort_unless(File::exists($path), 404);

    $excursions = json_decode(File::get($path), true);
    $excursion  = collect($excursions)->firstWhere('slug', $slug);
    abort_unless($excursion, 404);

    return view('pages.excursion.excursion-detail', compact('excursion'));
});

/** Statik sayfalar */
LocalizedRoute::view('contact', 'contact', 'pages.contact.index');
LocalizedRoute::view('help', 'help', 'pages.help.index');
LocalizedRoute::view('payment', 'payment', 'pages.payment.index');
LocalizedRoute::view('success', 'success', 'pages.payment.success');
LocalizedRoute::view('cart', 'cart', 'pages.cart.index');

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

    LocalizedRoute::view('account.coupons', 'account/coupons', 'pages.account.coupons');

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
