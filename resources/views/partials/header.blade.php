<!-- Header Top - Kampanya Carousel -->
<div class="bg-light text-dark py-1" style="font-size: 0.85rem;">
    <div class="container position-relative overflow-hidden" style="height: 30px;">
        <div id="campaignCarousel"
             class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
            <a href="#" class="text-dark text-decoration-none campaign-slide active">📢 %30 Erken Rezervasyon İndirimi!</a>
            <a href="#" class="text-dark text-decoration-none campaign-slide">🏖️ 7 Gece Kal, 5 Gece Öde Kampanyası Başladı!</a>
            <a href="#" class="text-dark text-decoration-none campaign-slide">✈️ Ücretsiz Havalimanı Transferi!</a>
            <a href="#" class="text-dark text-decoration-none campaign-slide">🏡 Kaş Villalarında %20 İndirim!</a></div>
    </div>
</div>
<!-- Main Header (Masaüstü için) -->
<nav class="navbar navbar-expand-lg bg-white shadow-sm d-none d-xl-flex flex-column border-bottom border-light">
    <!-- Üst satır (utility bar) -->
    <div class="container d-flex justify-content-end gap-3 small pt-2" style="margin-bottom: -15px;">
        <a href="https://wa.me/905551112233" target="_blank" class="btn btn-outline-success btn-sm text-decoration-none">
            <i class="fi fi-brands-whatsapp fs-5 align-middle"></i> <span>Whatsapp Destek</span>
        </a>
        <div class="vr"></div>
        <a href="#" class="text-dark text-decoration-none">Ödeme Yap</a>
        <div class="vr"></div>
        <a href="{{ route('help') }}" class="text-dark text-decoration-none">Yardım</a>
        <div class="vr"></div>
        <a href="{{ route('contact') }}" class="text-dark text-decoration-none">İletişim</a>
        <div class="vr"></div>

        <!-- Dil -->
        @php
        use App\Support\Helpers\LocaleHelper;

        $locale = app()->getLocale();
        $languages = LocaleHelper::options();
        @endphp

        <div class="dropdown">
            <a data-bs-toggle="dropdown" role="button"
               class="text-dark d-flex align-items-center gap-1" id="langCurrencyDropdown">
                @php
                $current = $languages[$locale] ?? reset($languages);
                @endphp
                @if($current)
                <img id="selectedFlag"
                     src="{{ $current['flag'] }}"
                     alt="{{ strtoupper($current['code']) }}"
                     width="28" height="28">
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-end p-3"
                 data-bs-auto-close="outside"
                 aria-labelledby="langCurrencyDropdown"
                 style="min-width: 200px;">

                <div class="mb-2">
                    <div class="dropdown-header px-0">Dil Seçimi</div>
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

                <!-- PARA BİRİMİ -->
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
        <div class="vr"></div>
        @php
        /** @var \App\Models\User|null $authUser */
        $authUser = auth()->user();

        $initials = '';
        if ($authUser) {
        $fn = $authUser->first_name ?: (\Illuminate\Support\Str::beforeLast($authUser->name, ' ') ?: $authUser->name);
        $ln = $authUser->last_name ?: \Illuminate\Support\Str::afterLast($authUser->name, ' ');

        if (!empty($fn)) {
        $initials .= mb_substr($fn, 0, 1);
        }
        if (!empty($ln) && $ln !== $fn) {
        $initials .= mb_substr($ln, 0, 1);
        }
        $initials = strtoupper($initials);
        }
        @endphp

        {{-- Sepet Butonu --}}
        <a href="{{ route('cart') }}" class="btn btn-sm btn-outline-primary position-relative">
            <i class="fi fi-rr-basket-shopping-simple" style="font-size: 18px; vertical-align: text-top;"></i>
            {{-- Sepet adedi ileride dinamik gelir --}}
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
        3
            <span class="visually-hidden">Sepette ürün sayısı</span>
        </span>
        </a>

        <div class="vr"></div>

        {{-- Kullanıcı / Giriş --}}
        @guest
        <a href="{{ route('login') }}" class="text-dark text-decoration-none">Giriş Yap</a>
        <div class="vr"></div>
        <a href="{{ route('register') }}" class="text-dark text-decoration-none">Kayıt Ol</a>
        @endguest

        <!-- Kullanıcı -->
        @auth
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none text-muted"
               id="userDropdownTop" data-bs-toggle="dropdown">

                <div class="avatar-fallback bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width:28px;height:28px;font-size:0.8rem;">
                    {{ $initials ?: strtoupper(mb_substr($authUser->name,0,1)) }}
                </div>

                <span>{{ $authUser->first_name ?? $authUser->name }}</span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdownTop">
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ route('account.dashboard') }}">
                        <i class="fi fi-rr-user"></i> Hesabım
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ route('account.bookings') }}">
                        <i class="fi fi-rr-calendar"></i> Rezervasyonlarım
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ route('account.coupons') }}">
                        <i class="fi fi-rr-ticket"></i> İndirim Kuponlarım
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ route('account.tickets') }}">
                        <i class="fi fi-rr-headset"></i> Destek Taleplerim
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2 px-3 py-2"
                       href="{{ route('account.settings') }}">
                        <i class="fi fi-rr-settings"></i> Üyelik Ayarlarım
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="dropdown-item text-danger d-flex align-items-center gap-2">
                            <i class="fi fi-rr-exit"></i> Çıkış Yap
                        </button>
                    </form>
                </li>
            </ul>
        </div>
        @endauth
    </div>

    <!-- Alt satır: logo + menü -->
    <div class="container d-flex justify-content-between align-items-end pb-1">
        <!-- Logo -->
        <a class="navbar-brand fw-bold fs-1" href="/" style="line-height: 0.6"><span
                class="text-primary">Icmeler</span><span class="text-warning">Online</span> <br><i
                class="fs-6 text-primary">30 years serving icmeler...</i></a>
        <!-- Menü -->
        <div class="navbar-collapse justify-content-end">
            <ul class="navbar-nav gap-3 bg-warning rounded-3 py-1 px-3 z-1 main-menu" style="margin-bottom: -20px">
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-center h-100 {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}"><i class="fi fi-ss-house-chimney"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('hotels') ? 'active' : '' }}" href="{{ route('hotels') }}">{{ t('nav.accommodation') }}</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('transfers') ? 'active' : '' }}" href="{{ route('transfers') }}">Havalimanı Transferi</a></li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('villa') ? 'active' : '' }}" href="{{ route('villa') }}">Kiralık Villalar</a></li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('excursions') ? 'active' : '' }}" href="{{ route('excursions') }}">Günlük Turlar</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('guides') ? 'active' : '' }} position-relative" href="{{ route('guides') }}">Gezi Rehberi</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Mobil Header -->
<nav class="navbar bg-white border-bottom py-2 px-3 d-flex d-xl-none align-items-center justify-content-between">
    <!-- Logo -->
    <a class="navbar-brand fw-bold fs-1" href="/" style="line-height: 0.6">
        <span class="text-primary">Icmeler</span><span class="text-warning">Online</span> <br>
        <i class="fs-6">30 years serving icmeler...</i>
    </a>

    <!-- Sağ: Dil + Kullanıcı -->
    <div class="d-flex align-items-center gap-2">
        <!-- Dil -->
        <div class="dropdown"><a data-bs-toggle="dropdown" role="button"
                                 class="text-dark d-flex align-items-center gap-1" id="langCurrencyDropdown"> <img
                    id="selectedFlag" src="/images/flags/tr.svg" alt="TR" width="32" height="32"> </a>
            <div class="dropdown-menu dropdown-menu-end p-3" data-bs-auto-close="outside"
                 aria-labelledby="langCurrencyDropdown" style="min-width: 200px;"> <!-- DİL SEÇİMİ -->
                <div class="mb-2">
                    <div class="dropdown-header px-0">Dil Seçimi</div>
                    <div class="btn-group w-100" role="group">
                        <button type="button"
                                class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 active"
                                data-lang="tr" data-flag="/images/flags/tr.svg"><img src="/images/flags/tr.svg" alt="TR"
                                                                                     width="20" height="20"> Türkçe
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
                                data-lang="en" data-flag="/images/flags/uk.svg"><img src="/images/flags/uk.svg" alt="EN"
                                                                                     width="20" height="20"> English
                        </button>
                    </div>
                </div>

                <!-- PARA BİRİMİ -->
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
            <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Giriş / Üye Ol</a>
            @endguest

            @auth
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none text-muted"
               id="userDropdownTopMobile" data-bs-toggle="dropdown">
                <div class="avatar-fallback bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                     style="width:28px;height:28px;font-size:0.8rem;">
                    {{ $initials ?: strtoupper(mb_substr($authUser->name,0,1)) }}
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdownTopMobile">
                <li><a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ route('account.dashboard') }}"><i class="fi fi-rr-user"></i> Hesabım</a></li>
                <li><a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ route('account.bookings') }}"><i class="fi fi-rr-calendar"></i> Rezervasyonlarım</a></li>
                <li><a class="dropdown-item d-flex align-items-center gap-2 py-2"
                       href="{{ route('account.settings') }}"><i class="fi fi-rr-id-badge"></i> Üyelik Bilgilerim</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="dropdown-item text-danger d-flex align-items-center gap-2">
                            <i class="fi fi-rr-exit"></i> Çıkış Yap
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
        <a class="navbar-brand fw-bold fs-1" href="/" style="line-height: 0.6">
            <span class="text-primary">Icmeler</span><span class="text-warning">Online</span> <br>
            <i class="fs-6">30 years serving icmeler...</i>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column gap-3">

        <!-- WhatsApp -->
        <a href="https://wa.me/905551112233" target="_blank"
           class="d-flex align-items-center gap-2 text-success text-decoration-none fw-semibold">
            <i class="fi fi-brands-whatsapp"></i> +90 555 111 2233
        </a>

        <!-- Menü Başlıkları -->
        <a class="nav-link" href="/">Anasayfa</a>
        <a class="nav-link" href="#">Oteller</a> <a class="nav-link" href="#">Havalimanı Transferi</a>
        <a class="nav-link" href="#">Kiralık Villalar</a> <a class="nav-link" href="#">Günlük Turlar</a>
        <a class="nav-link" href="#">Gezi Rehberi</a> <a class="nav-link" href="#">Ödeme Yap</a>
        <a class="nav-link" href="#">İletişim</a>
    </div>
</div>
