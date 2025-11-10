<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Support\UIconRegistry;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Account\SettingsController;


use App\Support\Helpers\LocaleHelper;

Route::get('/locale/{locale}', function (string $locale) {
    $active = LocaleHelper::active();

    if (in_array($locale, $active, true)) {
        // Oturumdaki kullanıcı için kalıcı yap
        if (auth()->check()) {
            auth()->user()->forceFill(['locale' => $locale])->save();
        }

        // Misafir veya anlık kullanım için
        session(['locale' => $locale]);

        app()->setLocale($locale);
    }

    return back();
})->name('locale.switch');


/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
|
| Tüm login/register/forgot/reset/verify rotaları
| routes/auth.php içinde tanımlı.
|
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Site Routes (FE)
|--------------------------------------------------------------------------
*/

Route::view('/', 'pages.home')->name('home');

/** Transfers (statik view varsa aç, yoksa 404) */
Route::get('/transferler', function () {
    if (view()->exists('pages.transfer.index')) {
        return view('pages.transfer.index');
    }

    abort(404);
})->name('transfers');

/** Hotels list + filters from public JSON */
Route::get('/hotels', function () {
    $path = public_path('data/hotels.json');
    abort_unless(File::exists($path), 404);

    $hotels = collect(json_decode(File::get($path)));

    // Yıldız filtresi (çoklu)
    if (request()->filled('stars')) {
        $stars = request()->input('stars');
        $stars = is_array($stars) ? $stars : [$stars];

        $hotels = $hotels->filter(fn ($hotel) => in_array($hotel->stars, $stars));
    }

    // Kategori filtresi (tekli)
    if (request()->filled('category')) {
        $category = request()->input('category');
        $hotels = $hotels->filter(fn ($hotel) => $hotel->category === $category);
    }

    return view('pages.hotel.index', compact('hotels'));
})->name('hotels');

/** Hotel detail */
Route::get('/hotel/{id}', function ($id) {
    $path = public_path('data/hotels.json');
    abort_unless(File::exists($path), 404);

    $hotels = json_decode(File::get($path), true);
    $hotel  = collect($hotels)->firstWhere('id', (int) $id);

    abort_unless($hotel, 404);

    return view('pages.hotel.hotel-detail', compact('hotel'));
})->name('hotel.detail');

/** Villas */
Route::get('/villalar', function () {
    $path = public_path('data/villas/villas.json');
    abort_unless(File::exists($path), 404);

    $villas = json_decode(File::get($path), true);

    return view('pages.villa.index', compact('villas'));
})->name('villa');

Route::get('/villa/{slug}', function ($slug) {
    $path = public_path('data/villas/villas.json');
    abort_unless(File::exists($path), 404);

    $villas = json_decode(File::get($path), true);
    $villa  = collect($villas)->firstWhere('slug', $slug);

    abort_unless($villa, 404);

    return view('pages.villa.villa-detail', compact('villa'));
})->name('villa.villa-detail');

/** Excursions */
Route::get('/excursions', function () {
    $path = public_path('data/excursions/excursions.json');
    abort_unless(File::exists($path), 404);

    $excursions = json_decode(File::get($path), true);

    return view('pages.excursion.index', compact('excursions'));
})->name('excursions');

Route::get('/excursions/{slug}', function ($slug) {
    $path = public_path('data/excursions/excursions.json');
    abort_unless(File::exists($path), 404);

    $excursions = json_decode(File::get($path), true);
    $excursion  = collect($excursions)->firstWhere('slug', $slug);

    abort_unless($excursion, 404);

    return view('pages.excursion.excursion-detail', compact('excursion'));
})->name('excursions.detail');

/** Basit statik sayfalar */
Route::view('/contact', 'pages.contact.index')->name('contact');
Route::view('/help', 'pages.help.index')->name('help');
Route::view('/payment', 'pages.payment.index')->name('payment');
Route::view('/success', 'pages.payment.success')->name('success');
Route::view('/cart', 'pages.cart.index')->name('cart');

/*
|--------------------------------------------------------------------------
| Account alanı — KİLİTLİ
|--------------------------------------------------------------------------
*/

Route::prefix('account')
    ->name('account.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::redirect('/', '/account/dashboard');

        Route::view('/dashboard', 'pages.account.index')->name('dashboard');
        Route::view('/bookings', 'pages.account.bookings')->name('bookings');
        Route::view('/coupons', 'pages.account.coupons')->name('coupons');
        Route::view('/tickets', 'pages.account.tickets')->name('tickets');

        Route::view('/settings', 'pages.account.settings')->name('settings');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

        Route::get('/tickets/{id}', function ($id) {
            return view('pages.account.ticket-detail', ['id' => $id]);
        })->name('tickets.show');
    });

/*
|--------------------------------------------------------------------------
| Guides
|--------------------------------------------------------------------------
*/

Route::get('/gezi-rehberi', function () {
    $guides = [
        [
            'title'        => 'Marmaris',
            'slug'         => 'marmaris-gezi-rehberi',
            'excerpt'      => 'Marmaris’te gezilecek yerler, plajlar, yeme-içme ve turlar...',
            'cover'        => '/images/samples/popular-marmaris.jpg',
            'category'     => 'Ege',
            'published_at' => '2025-08-01',
        ],
        [
            'title'        => 'İçmeler',
            'slug'         => 'icmeler-gezi-rehberi',
            'excerpt'      => 'İçmeler’in en popüler plajları ve aktiviteleri.',
            'cover'        => '/images/samples/icmeler-1.jpg',
            'category'     => 'Ege',
            'published_at' => '2025-07-20',
        ],
    ];

    return view('pages.guides.index', compact('guides'));
})->name('guides');

Route::get('/gezi-rehberi/{slug}', function ($slug) {
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

    $hotelsPath     = public_path('data/hotels.json');
    $villasPath     = public_path('data/villas/villas.json');
    $excursionsPath = public_path('data/excursions/excursions.json');

    $hotels     = File::exists($hotelsPath) ? json_decode(File::get($hotelsPath)) : [];
    $villas     = File::exists($villasPath) ? json_decode(File::get($villasPath), true) : [];
    $excursions = File::exists($excursionsPath) ? json_decode(File::get($excursionsPath), true) : [];

    $hotel            = $hotels[0] ?? null;
    $villa            = $villas[0] ?? null;
    $excursionsSidebar = array_slice($excursions, 0, 2);

    return view('pages.guides.show', compact('guide', 'slug', 'hotel', 'villa', 'excursionsSidebar'));
})->name('guides.show');

/*
|--------------------------------------------------------------------------
| Admin Utilities
|--------------------------------------------------------------------------
*/

Route::get('/admin/uicons', function (Request $r) {
    $variant = $r->string('variant', 'outline'); // outline | solid | bold | straight | thin | all
    $icons   = UIconRegistry::list($variant);

    return response()->json($icons, 200, [
        'Cache-Control' => 'public, max-age=86400',
        'ETag'          => md5($variant.'|'.count($icons)),
    ]);
})->middleware(['auth']);

/*
|--------------------------------------------------------------------------
| Debug (local)
|--------------------------------------------------------------------------
*/

if (app()->environment('local')) {
    Route::get('/whoami', fn () => [
        'id'    => auth()->id(),
        'email' => auth()->user()?->email,
        'roles' => auth()->user()?->getRoleNames(),
    ])->middleware('auth');
}
