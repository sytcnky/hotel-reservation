{{-- resources/views/partials/header.blade.php --}}
@php
    use App\Support\Currency\CurrencyPresenter;
    use App\Support\Helpers\LocaleHelper;
    use App\Support\Helpers\CurrencyHelper;
    use App\Models\Setting;
    use Illuminate\Support\Str;

    $currentRoute = Str::after(request()->route()->getName(), app()->getLocale() . '.');
    $currencies      = CurrencyHelper::activeOptions();
    $currentCurrency = CurrencyHelper::currentCode();

    /** @var \App\Models\User|null $authUser */
    $authUser = auth()->user();

    $locale      = app()->getLocale();
    $languages   = LocaleHelper::options();
    $currentLang = $languages[$locale] ?? reset($languages);

    $baseLocale = LocaleHelper::defaultCode();

    $pick = function (string $key) use ($locale, $baseLocale): ?string {
        $map = Setting::get($key, []);
        if (! is_array($map)) {
            return null;
        }

        // kontrat: ui → base
        $val = $map[$locale] ?? $map[$baseLocale] ?? null;

        return is_string($val) && trim($val) !== '' ? trim($val) : null;
    };

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

    $cartItems = (array) session('cart.items', []);
    $cartCount = count($cartItems);

    $waLabel = $pick('contact_whatsapp_label');
    $waPhone = $pick('contact_whatsapp_phone');

    $waNumber = $waPhone ? preg_replace('/[^0-9]/', '', $waPhone) : null;
    $waUrl    = $waNumber ? ('https://wa.me/' . $waNumber) : null;
@endphp

    <!-- Main Header (Desktop) -->
<nav class="navbar bg-white shadow-lg border-bottom border-light site-header-desktop">
    <div class="container py-2 header-row d-flex align-items-center align-items-xl-start justify-content-between gap-4">

        {{-- Logo --}}
        <a href="{{ localized_route('home') }}" class="navbar-brand lh-1 m-0 flex-row">
            <div class="fw-bold fs-1">
                <span class="text-primary">Icmeler</span><span class="text-warning">Online</span>
            </div>
            <small class="text-primary fst-italic">30 years serving Icmeler...</small>
        </a>

        {{-- Utility (üst) + Menü (alt) --}}
        <div class="header-right d-flex flex-column align-items-end">

            {{-- Utility bar (üst) --}}
            <div class="d-flex justify-content-end align-items-center gap-3 small">

                {{-- WhatsApp Destek --}}
                @if ($waUrl && $waLabel)
                    <a href="{{ $waUrl }}"
                       target="_blank"
                       rel="noopener"
                       class="btn btn-outline-success btn-sm text-decoration-none d-none d-xl-block">
                        <i class="fi fi-brands-whatsapp fs-5 align-middle"></i>
                        <span>{{ $waLabel }}</span>
                    </a>
                @endif

                <div class="vr d-none d-xl-block"></div>

                {{-- Yardım --}}
                <a href="{{ localized_route('help') }}" class="text-dark text-decoration-none d-none d-xl-block">
                    {{ t('nav.help') }}
                </a>

                <div class="vr d-none d-xl-block"></div>

                {{-- İletişim --}}
                <a href="{{ localized_route('contact') }}" class="text-dark text-decoration-none d-none d-xl-block">
                    {{ t('nav.contact') }}
                </a>

                <div class="vr d-none d-xl-block"></div>

                {{-- Sepet --}}
                <a href="{{ localized_route('cart') }}"
                   class="btn btn-sm btn-outline-primary position-relative d-none d-xl-block">
                    <i class="fi fi-rr-basket-shopping-simple" style="font-size: 18px; vertical-align: text-top;"></i>

                    @if($cartCount > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ $cartCount }}
                            <span class="visually-hidden">Sepette ürün sayısı</span>
                        </span>
                    @endif
                </a>

                <div class="vr d-none d-xl-block"></div>

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
                            <div class="btn-group" role="group" data-lang-toggle>
                                @foreach($languages as $code => $lang)
                                    <a href="{{ locale_switch_url($code) }}"
                                       class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1 {{ $locale === $code ? 'active' : '' }}">
                                        @if($lang['flag'])
                                            <img src="{{ $lang['flag'] }}" alt="{{ strtoupper($code) }}" width="20"
                                                 height="20">
                                        @endif
                                        {{ $lang['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Para Birimi --}}
                        <div>
                            <div class="dropdown-header px-0">{{ t('nav.currency') }}</div>

                            <form method="POST" class="m-0 p-0" id="currencySwitchForm">
                                @csrf
                                <input type="hidden" name="confirm" value="0" id="currencyConfirmField">

                                <div class="btn-group w-100" role="group">
                                    @foreach ($currencies as $c)
                                        @php
                                            $isActive = $currentCurrency === $c['code'];
                                        @endphp

                                        <button type="submit"
                                                class="btn btn-outline-secondary btn-sm {{ $isActive ? 'active' : '' }} {{ $cartCount > 0 ? 'js-currency-switch' : '' }}"
                                                data-currency-action="{{ route('currency.switch', $c['code']) }}"
                                                @if ($cartCount === 0)
                                                    formaction="{{ route('currency.switch', $c['code']) }}"
                                            @endif
                                        >
                                            {{ \App\Support\Currency\CurrencyPresenter::label($c['code']) }}
                                        </button>
                                    @endforeach
                                </div>
                            </form>
                        </div>

                    </div>
                </div>

                <div class="vr d-none d-xl-block"></div>

                {{-- Giriş / Kullanıcı --}}
                @guest
                    <a href="{{ route('login') }}" class="text-dark text-decoration-none d-none d-xl-block">
                        {{ t('nav.auth.login') }}
                    </a>
                @endguest

                @auth
                    <div class="dropdown">
                        <a href="#"
                           class="d-flex align-items-center gap-2 text-decoration-none text-muted"
                           id="userDropdownTop"
                           data-bs-toggle="dropdown">
                            <div
                                class="avatar-fallback bg-primary rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                                style="width:28px;height:28px;font-size:0.8rem;">
                                {{ $initials ?: mb_strtoupper(mb_substr($authUser->name, 0, 1)) }}
                            </div>
                            <span class="d-none d-md-block">{{ $authUser->first_name ?? $authUser->name }}</span>
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
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="dropdown-item text-danger d-flex align-items-center gap-2">
                                        <i class="fi fi-rr-exit"></i>
                                        {{ t('nav.auth.logout') }}
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>

            {{-- Ana menü (alt) --}}
            <ul class="navbar-nav flex-row gap-3 bg-warning rounded-3 py-2 px-3 main-menu mt-3 d-none d-xl-flex">
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

<!-- Offcanvas Menü (Mobil) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mainOffcanvas" aria-labelledby="mainOffcanvasLabel">
    <div class="offcanvas-header">
        {{-- Logo --}}
        <a href="{{ localized_route('home') }}" class="navbar-brand lh-1 m-0 flex-row">
            <div class="fw-bold fs-1">
                <span class="text-primary">Icmeler</span><span class="text-warning">Online</span>
            </div>
            <small class="text-primary fst-italic">30 years serving Icmeler...</small>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Kapat"></button>
    </div>
    <hr>
    <div class="offcanvas-body d-flex flex-column gap-3">
        <ul class="nav flex-column">
            {{-- Menü --}}
            <li class="nav-item">
                <a class="nav-link d-flex align-items-center h-100 {{ $currentRoute === 'home' ? 'active' : '' }}"
                   href="{{ localized_route('home') }}">
                    {{ t('nav.home') }}
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

        <hr>

        <div class="vr d-none d-xl-block"></div>

        {{-- Yardım --}}
        <a href="{{ localized_route('help') }}" class="text-dark text-decoration-none">
            {{ t('nav.help') }}
        </a>

        <div class="vr d-none d-xl-block"></div>

        {{-- İletişim --}}
        <a href="{{ localized_route('contact') }}" class="text-dark text-decoration-none">
            {{ t('nav.contact') }}
        </a>


        {{-- WhatsApp Destek --}}
        @if ($waUrl && $waLabel)
            <a href="{{ $waUrl }}"
               target="_blank"
               rel="noopener"
               class="btn btn-outline-success btn-sm text-decoration-none">
                <i class="fi fi-brands-whatsapp fs-5 align-middle"></i>
                <span>{{ $waLabel }}</span>
            </a>
        @endif
    </div>
</div>

@if($cartCount > 0)
    <div class="modal fade" id="currencyChangeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ t('nav.currency') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">
                        {{ t('ui.currency_switch_modal_title') }}
                        <br><br>
                        {{ t('ui.currency_switch_modal_text') }}
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ t('ui.currency_switch_modal_cancel') }}
                    </button>
                    <a href="#" class="btn btn-primary" id="confirmCurrencyChange">
                        {{ t('ui.currency_switch_modal_confirm') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif
