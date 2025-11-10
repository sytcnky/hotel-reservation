{{-- resources/views/partials/side-menu.blade.php --}}
@php
$menu = [
[
'name'  => 'account.dashboard',
'is'    => 'account.dashboard',
'label' => 'customer_account.menu.dashboard',
'icon'  => 'fi fi-rr-user',
],
[
'name'  => 'account.bookings',
'is'    => 'account.bookings*',
'label' => 'customer_account.menu.bookings',
'icon'  => 'fi fi-rr-calendar',
],
[
'name'  => 'account.coupons',
'is'    => 'account.coupons*',
'label' => 'customer_account.menu.coupons',
'icon'  => 'fi fi-rr-ticket',
],
[
'name'  => 'account.tickets',
'is'    => 'account.tickets*',
'label' => 'customer_account.menu.tickets',
'icon'  => 'fi fi-rr-headset',
'badge' => 1, // ileride dinamik yapılacak
],
[
'name'  => 'account.settings',
'is'    => 'account.settings*',
'label' => 'customer_account.menu.settings',
'icon'  => 'fi fi-rr-settings',
],
];

$activeIndex = 0;
foreach ($menu as $i => $it) {
if (request()->routeIs($it['is'])) {
$activeIndex = $i;
break;
}
}
$active = $menu[$activeIndex];
@endphp

{{-- DESKTOP (lg+): tam liste --}}
<nav class="d-none d-lg-grid gap-2">
    @foreach ($menu as $it)
    @php $isActive = request()->routeIs($it['is']); @endphp
    <a href="{{ route($it['name']) }}"
       class="btn w-100 d-flex align-items-center justify-content-start gap-2 p-3 {{ $isActive ? 'btn-primary text-white' : 'btn-outline-primary' }}">
        <i class="{{ $it['icon'] }} fs-5"></i>
        <span class="fw-semibold">{{ t($it['label']) }}</span>
        @if (!empty($it['badge']))
        <span class="badge text-bg-danger ms-auto">{{ $it['badge'] }}</span>
        @endif
    </a>
    @endforeach
</nav>

{{-- MOBILE/TABLET (lg-): Aktif öğe = collapse toggler (buton), diğerleri aşağıda --}}
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
        <a href="{{ route($it['name']) }}"
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
