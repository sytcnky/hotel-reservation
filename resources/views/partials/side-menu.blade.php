@php
use Illuminate\Support\Str;

$current = Str::after(request()->route()->getName(), app()->getLocale() . '.');

$menu = [
[
'name'  => 'account.dashboard',
'match' => 'account.dashboard',
'label' => 'customer_account.menu.dashboard',
'icon'  => 'fi fi-rr-user',
],
[
'name'  => 'account.bookings',
'match' => 'account.bookings*',
'label' => 'customer_account.menu.bookings',
'icon'  => 'fi fi-rr-calendar',
],
[
'name'  => 'account.coupons',
'match' => 'account.coupons*',
'label' => 'customer_account.menu.coupons',
'icon'  => 'fi fi-rr-ticket',
],
[
'name'  => 'account.tickets',
'match' => 'account.tickets*',
'label' => 'customer_account.menu.tickets',
'icon'  => 'fi fi-rr-headset',
'badge' => 1,
],
[
'name'  => 'account.settings',
'match' => 'account.settings*',
'label' => 'customer_account.menu.settings',
'icon'  => 'fi fi-rr-settings',
],
];

$activeIndex = collect($menu)
->search(fn ($it) => Str::is($it['match'], $current))
?? 0;

$active = $menu[$activeIndex];
@endphp

{{-- DESKTOP --}}
<nav class="d-none d-lg-grid gap-2">
    @foreach ($menu as $it)
    @php $isActive = Str::is($it['match'], $current); @endphp
    <a href="{{ localized_route($it['name']) }}"
       class="btn w-100 d-flex align-items-center justify-content-start gap-2 p-3 {{ $isActive ? 'btn-primary text-white' : 'btn-outline-primary' }}">
        <i class="{{ $it['icon'] }} fs-5"></i>
        <span class="fw-semibold">{{ t($it['label']) }}</span>
        @if (!empty($it['badge']))
        <span class="badge text-bg-danger ms-auto">{{ $it['badge'] }}</span>
        @endif
    </a>
    @endforeach
</nav>

{{-- MOBILE --}}
<div class="d-lg-none">
    <button type="button"
            class="btn w-100 d-flex align-items-center justify-content-start gap-2 p-3 btn-primary text-white"
            data-bs-toggle="collapse"
            data-bs-target="#accountMobileMenu"
            aria-expanded="false"
            aria-controls="accountMobileMenu">
        <i class="{{ $active['icon'] }} fs-5"></i>
        <span class="fw-semibold">{{ t($active['label']) }}</span>
        @if (!empty($active['badge']))
        <span class="badge text-bg-danger ms-auto me-2">{{ $active['badge'] }}</span>
        @else
        <span class="ms-auto"></span>
        @endif
        <i class="fi fi-rr-menu-burger fs-5"></i>
    </button>

    <div class="collapse mt-2" id="accountMobileMenu">
        @foreach ($menu as $i => $it)
        @continue($i === $activeIndex)
        <a href="{{ localized_route($it['name']) }}"
           class="btn w-100 d-flex align-items-center justify-content-start gap-2 p-3 mb-2 btn-outline-primary">
            <i class="{{ $it['icon'] }} fs-5"></i>
            <span class="fw-semibold">{{ t($it['label']) }}</span>
            @if (!empty($it['badge']))
            <span class="badge text-bg-danger ms-auto">{{ $it['badge'] }}</span>
            @endif
        </a>
        @endforeach
    </div>
</div>
