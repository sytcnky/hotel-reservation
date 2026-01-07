@php
    $cartItems = (array) session('cart.items', []);
    $cartCount = count($cartItems);

    $items = [
        [
            'label' => 'Hesabım',
            'icon'  => 'fi fi-rr-user',
            'href'  => localized_route('account.dashboard'),
            'active'=> request()->routeIs('*.account.*'),
        ],
        [
            'label' => 'Rezervasyonlarım',
            'icon'  => 'fi fi-rr-calendar-check',
            'href'  => localized_route('account.bookings'),
            'active'=> request()->routeIs('*.account.bookings*'),
        ],
        [
            'label' => 'Sepet',
            'icon'  => 'fi fi-rr-basket-shopping-simple',
            'href'  => localized_route('cart'),
            'active'=> request()->routeIs('*.cart*'),
            'badge' => $cartCount > 0 ? $cartCount : null,
        ],
    ];
@endphp

<nav class="mobile-bottom-nav d-xl-none fixed-bottom border-top bg-white" role="navigation" aria-label="Alt menü">
    <ul class="mb-0 list-unstyled d-flex align-items-stretch justify-content-around">
        {{-- MENÜ butonu (offcanvas açar) --}}
        <li class="flex-fill">
            <button
                type="button"
                class="mobile-bottom-link border-0 bg-transparent w-100"
                data-bs-toggle="offcanvas"
                data-bs-target="#mainOffcanvas"
                aria-controls="mainOffcanvas"
                aria-label="Menüyü aç">
                <span class="mobile-bottom-icon"><i class="fi fi-rr-menu-burger" aria-hidden="true"></i></span>
                <span class="mobile-bottom-label">Menü</span>
            </button>
        </li>

        {{-- Diğer öğeler --}}
        @foreach ($items as $item)
        <li class="flex-fill">
            <a href="{{ $item['href'] }}"
               class="mobile-bottom-link {{ !empty($item['active']) ? 'is-active' : '' }}"
               aria-current="{{ !empty($item['active']) ? 'page' : 'false' }}">
                    <span class="mobile-bottom-icon position-relative">
                        <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                        @if(!empty($item['badge']))
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: .7rem">
                                {{ $item['badge'] }}
                                <span class="visually-hidden">Sepette ürün sayısı</span>
                            </span>
                        @endif
                    </span>
                <span class="mobile-bottom-label">{{ $item['label'] }}</span>
            </a>
        </li>
        @endforeach
    </ul>
</nav>
