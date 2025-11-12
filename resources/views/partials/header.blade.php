{{-- resources/views/partials/header.blade.php --}}
@php
use App\Support\Helpers\LocaleHelper;
use App\Support\Helpers\CurrencyHelper;
use Illuminate\Support\Str;

$currentRoute = Str::after(request()->route()->getName(), app()->getLocale() . '.');
$currencies      = \App\Support\Helpers\CurrencyHelper::active();
$currentCurrency = \App\Support\Helpers\CurrencyHelper::currentCode();

/** @var \App\Models\User|null $authUser */
$authUser = auth()->user();

$locale      = app()->getLocale();
$languages   = LocaleHelper::options();
$currentLang = $languages[$locale] ?? reset($languages);

$initials = '';
if ($authUser) {
$fn = $authUser->first_name ?: (Str::beforeLast($authUser->name, ' ') ?: $authUser->name);
$ln = $authUser->last_name ?: Str::afterLast($authUser->name, ' ');

if (!empty($fn)) {
$initials .= mb_substr($fn, 0, 1);
}
if (!empty($ln) && $ln !== $fn) {
$initials .= mb_substr($ln, 0, 1);
}
$initials = mb_strtoupper($initials);
}
@endphp

<!-- Header Top - Kampanya Carousel (şimdilik statik) -->
<div class="bg-light text-dark py-1" style="font-size: 0.85rem;">
    <div class="container position-relative overflow-hidden" style="height: 30px;">
        <div id="campaignCarousel"
             class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
            <a href="#" class="text-dark text-decoration-none campaign-slide active">📢 %30 Erken Rezervasyon İndirimi!</a>
            <a href="#" class="text-dark text-decoration-none campaign-slide">🏖️ 7 Gece Kal, 5 Gece Öde Kampanyası Başladı!</a>
            <a href="#" class="text-dark text-decoration-none campaign-slide">✈️ Ücretsiz Havalimanı Transferi!</a>
            <a href="#" class="text-dark text-decoration-none campaign-slide">🏡 Kaş Villalarında %20 İndirim!</a>
        </div>
    </div>
</div>

<!-- Main Header (Desktop) -->
<nav class="navbar navbar-expand-lg bg-white shadow-lg d-none d-xl-flex flex-column border-bottom border-light">
    <!-- Üst satır (utility bar) -->
    <div class="container d-flex justify-content-end gap-3 small pt-2" style="margin-bottom: -15px;">

        {{-- WhatsApp Destek --}}
        <a href="https://wa.me/905551112233"
           target="_blank"
           class="btn btn-outline-success btn-sm text-decoration-none">
            <i class="fi fi-brands-whatsapp fs-5 align-middle"></i>
            <span>Whatsapp Destek</span>
        </a>

        <div class="vr"></div>

        {{-- Ödeme Yap --}}
        <a href="{{ localized_route('payment') }}" class="text-dark text-decoration-none">
            {{ t('nav.payment') }}
        </a>

        <div class="vr"></div>

        {{-- Yardım --}}
        <a href="{{ localized_route('help') }}" class="text-dark text-decoration-none">
            {{ t('nav.help') }}
        </a>

        <div class="vr"></div>

        {{-- İletişim --}}
        <a href="{{ localized_route('contact') }}" class="text-dark text-decoration-none">
            {{ t('nav.contact') }}
        </a>

        <div class="vr"></div>

        {{-- Dil & Para Birimi --}}
        <div class="dropdown">
            <a data-bs-toggle="dropdown"
               role="button"
               class="text-dark d-flex align-items-center gap-1"
               id="langCurrencyDropdown">
                @if($currentLang)
                <img id="selectedFlag"
                     src="{{ $currentLang['flag'] }}"
                     alt="{{ strtoupper($currentLang['code']) }}"
                     width="28" height="28">
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-end p-3"
                 data-bs-auto-close="outside"
                 aria-labelledby="langCurrencyDropdown"
                 style="min-width: 200px;">

                {{-- Dil Seçimi --}}
                <div class="mb-2">
                    <div class="dropdown-header px-0">{{ t('nav.language') }}</div>
                    <div class="btn-group" role="group">
                        @foreach($languages as $code => $lang)
                        <a href="{{ route('locale.switch', ['locale' => $code]) }}"
                           class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 {{ $locale === $code ? 'active' : '' }}">
                            @if($lang['flag'])
                            <img src="{{ $lang['flag'] }}" alt="{{ strtoupper($code) }}" width="20" height="20">
                            @endif
                            {{ $lang['label'] }}
                        </a>
                        @endforeach
                    </div>
                </div>

                {{-- Para Birimi (şimdilik statik) --}}
                <div>
                    <div class="dropdown-header px-0">{{ t('nav.currency') }}</div>
                    <div class="btn-group w-100" role="group">
                        @foreach ($currencies as $c)
                        <a href="{{ route('currency.switch', $c['code']) }}"
                           class="btn btn-outline-secondary btn-sm {{ $currentCurrency === $c['code'] ? 'active' : '' }}">
                            {{ $c['symbol'] }} {{ $c['code'] }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="vr"></div>

        {{-- Sepet --}}
        <a href="{{ localized_route('cart') }}" class="btn btn-sm btn-outline-primary position-relative">
            <i class="fi fi-rr-basket-shopping-simple" style="font-size: 18px; vertical-align: text-top;"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                3
                <span class="visually-hidden">Sepette ürün sayısı</span>
            </span>
        </a>

        <div class="vr"></div>

        {{-- Giriş / Kullanıcı --}}
        @guest
        <a href="{{ route('login') }}" class="text-dark text-decoration-none">
            {{ t('auth.login') }}
        </a>
        <div class="vr"></div>
        <a href="{{ route('register') }}" class="text-dark text-decoration-none">
            {{ t('auth.register') }}
        </a>
        @endguest

        @auth
        <div class="dropdown">
            <a href="#"
               class="d-flex align-items-center gap-2 text-decoration-none text-muted"
               id="userDropdownTop"
               data-bs-toggle="dropdown">
                <div class="avatar-fallback bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width:28px;height:28px;font-size:0.8rem;">
                    {{ $initials ?: mb_strtoupper(mb_substr($authUser->name, 0, 1)) }}
                </div>
                <span>{{ $authUser->first_name ?? $authUser->name }}</span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdownTop">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ localized_route('account.dashboard') }}">
                        <i class="fi fi-rr-user"></i>
                        {{ t('customer_account.menu.dashboard') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ localized_route('account.bookings') }}">
                        <i class="fi fi-rr-calendar"></i>
                        {{ t('customer_account.menu.bookings') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ localized_route('account.coupons') }}">
                        <i class="fi fi-rr-ticket"></i>
                        {{ t('customer_account.menu.coupons') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ localized_route('account.tickets') }}">
                        <i class="fi fi-rr-headset"></i>
                        {{ t('customer_account.menu.tickets') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 px-3 py-2"
                       href="{{ localized_route('account.settings') }}">
                        <i class="fi fi-rr-settings"></i>
                        {{ t('customer_account.menu.settings') }}
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="dropdown-item text-danger d-flex align-items-center gap-2">
                            <i class="fi fi-rr-exit"></i>
                            {{ t('auth.logout') }}
                        </button>
                    </form>
                </li>
            </ul>
        </div>
        @endauth
    </div>

    <!-- Alt satır: logo + ANA MENÜ -->
    <div class="container d-flex justify-content-between align-items-end pb-1">
        {{-- Logo --}}
        <a class="navbar-brand fw-bold fs-1"
           href="{{ localized_route('home') }}"
           style="line-height: 0.6">
            <span class="text-primary">Icmeler</span><span class="text-warning">Online</span> <br>
            <i class="fs-6 text-primary">30 years serving icmeler...</i>
        </a>

        {{-- Ana Menü (yalnızca orijinal öğeler) --}}
        <div class="navbar-collapse justify-content-end">
            <ul class="navbar-nav gap-3 bg-warning rounded-3 py-1 px-3 z-1 main-menu" style="margin-bottom: -20px">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center h-100 {{ $currentRoute === 'home' ? 'active' : '' }}"
                       href="{{ localized_route('home') }}">
                        <i class="fi fi-ss-house-chimney"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentRoute === 'hotels' ? 'active' : '' }}"
                       href="{{ localized_route('hotels') }}">
                        {{ t('nav.hotels') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentRoute === 'transfers' ? 'active' : '' }}"
                       href="{{ localized_route('transfers') }}">
                        {{ t('nav.transfers') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentRoute === 'villa' ? 'active' : '' }}"
                       href="{{ localized_route('villa') }}">
                        {{ t('nav.villas') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Str::startsWith($currentRoute, 'excursions') ? 'active' : '' }}"
                       href="{{ localized_route('excursions') }}">
                        {{ t('nav.excursions') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Str::startsWith($currentRoute, 'guides') ? 'active' : '' }}"
                       href="{{ localized_route('guides') }}">
                        {{ t('nav.guides') }}
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Mobil Header -->
<nav class="navbar bg-white border-bottom py-2 px-3 d-flex d-xl-none align-items-center justify-content-between">
    {{-- Logo --}}
    <a class="navbar-brand fw-bold fs-1"
       href="{{ localized_route('home') }}"
       style="line-height: 0.6">
        <span class="text-primary">Icmeler</span><span class="text-warning">Online</span> <br>
        <i class="fs-6">30 years serving icmeler...</i>
    </a>

    {{-- Sağ: Dil + Kullanıcı --}}
    <div class="d-flex align-items-center gap-2">
        {{-- Dil --}}
        <div class="dropdown">
            <a data-bs-toggle="dropdown"
               role="button"
               class="text-dark d-flex align-items-center gap-1"
               id="langCurrencyDropdownMobile">
                @if($currentLang)
                <img id="selectedFlagMobile"
                     src="{{ $currentLang['flag'] }}"
                     alt="{{ strtoupper($currentLang['code']) }}"
                     width="32"
                     height="32">
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-end p-3"
                 data-bs-auto-close="outside"
                 aria-labelledby="langCurrencyDropdownMobile"
                 style="min-width: 200px;">
                <div class="mb-2">
                    <div class="dropdown-header px-0">Dil Seçimi</div>
                    <div class="btn-group w-100" role="group">
                        @foreach($languages as $code => $lang)
                        <a href="{{ route('locale.switch', ['locale' => $code]) }}"
                           class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 {{ $locale === $code ? 'active' : '' }}">
                            @if($lang['flag'])
                            <img src="{{ $lang['flag'] }}" alt="{{ strtoupper($code) }}" width="20" height="20">
                            @endif
                            {{ $lang['label'] }}
                        </a>
                        @endforeach
                    </div>
                </div>
                <div>
                    <div class="dropdown-header px-0">Para Birimi</div>
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-secondary btn-sm active">₺ TL</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm">€ EUR</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm">$ USD</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Profil (mobil) --}}
        <div class="dropdown">
            @guest
            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                {{ t('auth.login_register') }}
            </a>
            @endguest

            @auth
            <a href="#"
               class="d-flex align-items-center gap-2 text-decoration-none text-muted"
               id="userDropdownTopMobile"
               data-bs-toggle="dropdown">
                <div class="avatar-fallback bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width:28px;height:28px;font-size:0.8rem;">
                    {{ $initials ?: mb_strtoupper(mb_substr($authUser->name,0,1)) }}
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdownTopMobile">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ localized_route('account.dashboard') }}">
                        <i class="fi fi-rr-user"></i>
                        {{ t('customer_account.menu.dashboard') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ localized_route('account.bookings') }}">
                        <i class="fi fi-rr-calendar"></i>
                        {{ t('customer_account.menu.bookings') }}
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ localized_route('account.settings') }}">
                        <i class="fi fi-rr-id-badge"></i>
                        {{ t('customer_account.menu.settings') }}
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="dropdown-item text-danger d-flex align-items-center gap-2">
                            <i class="fi fi-rr-exit"></i>
                            {{ t('auth.logout') }}
                        </button>
                    </form>
                </li>
            </ul>
            @endauth
        </div>
    </div>
</nav>

<!-- Offcanvas Menü (Mobil) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mainOffcanvas" aria-labelledby="mainOffcanvasLabel">
    <div class="offcanvas-header">
        <a class="navbar-brand fw-bold fs-1"
           href="{{ localized_route('home') }}"
           style="line-height: 0.6">
            <span class="text-primary">Icmeler</span><span class="text-warning">Online</span> <br>
            <i class="fs-6">30 years serving icmeler...</i>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column gap-3">
        {{-- WhatsApp --}}
        <a href="https://wa.me/905551112233" target="_blank"
           class="d-flex align-items-center gap-2 text-success text-decoration-none fw-semibold">
            <i class="fi fi-brands-whatsapp"></i> +90 555 111 2233
        </a>

        {{-- Menü Başlıkları (orijinal sıra) --}}
        <a class="nav-link" href="{{ localized_route('home') }}">{{ t('nav.home') }}</a>
        <a class="nav-link" href="{{ localized_route('hotels') }}">{{ t('nav.hotels') }}</a>
        <a class="nav-link" href="{{ localized_route('transfers') }}">Havalimanı Transferi</a>
        <a class="nav-link" href="{{ localized_route('villa') }}">Kiralık Villalar</a>
        <a class="nav-link" href="{{ localized_route('excursions') }}">Günlük Turlar</a>
        <a class="nav-link" href="{{ localized_route('guides') }}">Gezi Rehberi</a>
        <a class="nav-link" href="{{ localized_route('payment') }}">{{ t('nav.payment') }}</a>
        <a class="nav-link" href="{{ localized_route('contact') }}">{{ t('nav.contact') }}</a>
    </div>
</div>
