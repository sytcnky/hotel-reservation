{{-- resources/views/pages/account/index.blade.php --}}
@extends('layouts.account')

@section('account_content')
<style>
    .dash-card .card-body{ text-align:center; padding:24px; border-radius:.75rem; cursor:pointer; }
    .dash-card:hover .card-body,
    .dash-card:focus-within .card-body{ background: rgba(0,0,0,.03); }
    .dash-icon{ font-size:44px; line-height:1; display:block; }
    .dash-title{ margin-top:.75rem; font-weight:600; }
    .corner-badge{ position:absolute; top:.5rem; right:.5rem; }
</style>

@php
/** @var \App\Models\User|null $authUser */
$authUser = auth()->user();
$displayName = $authUser?->first_name ?: $authUser?->name ?: '';
@endphp

<section>
    <div class="my-4">
        <h1 class="display-5 fw-bold text-secondary">
            {{ t('customer_account.dashboard.greeting') }}
            @if($displayName)
            {{ ' ' . $displayName }}
            @endif
        </h1>
    </div>
</section>

<div class="row g-3 row-cols-1 row-cols-sm-2">
    {{-- Rezervasyonlarım --}}
    <div class="col">
        <div class="card shadow-sm position-relative dash-card">
            <div class="card-body text-secondary">
                <i class="fi fi-rr-calendar dash-icon" aria-hidden="true"></i>
                <div class="dash-title">{{ t('customer_account.menu.bookings') }}</div>
                <a href="{{ localized_route('account.bookings') }}"
                   class="stretched-link"
                   aria-label="{{ t('customer_account.menu.bookings') }}"></a>
            </div>
        </div>
    </div>

    {{-- İndirim Kuponlarım --}}
    <div class="col">
        <div class="card shadow-sm position-relative dash-card">
            <div class="card-body text-secondary">
                <i class="fi fi-rr-ticket dash-icon" aria-hidden="true"></i>
                <div class="dash-title">{{ t('customer_account.menu.coupons') }}</div>
                <a href="{{ localized_route('account.coupons') }}"
                   class="stretched-link"
                   aria-label="{{ t('customer_account.menu.coupons') }}"></a>
            </div>
        </div>
    </div>

    {{-- Destek Taleplerim (badge ileride dinamik olacak) --}}
    <div class="col">
        <div class="card shadow-sm position-relative dash-card">
            <span class="badge bg-danger corner-badge">1</span>
            <div class="card-body text-secondary">
                <i class="fi fi-rr-life-ring dash-icon" aria-hidden="true"></i>
                <div class="dash-title">{{ t('customer_account.menu.tickets') }}</div>
                <a href="{{ localized_route('account.tickets') }}"
                   class="stretched-link"
                   aria-label="{{ t('customer_account.menu.tickets') }}"></a>
            </div>
        </div>
    </div>

    {{-- Üyelik Ayarlarım --}}
    <div class="col">
        <div class="card shadow-sm position-relative dash-card">
            <div class="card-body text-secondary">
                <i class="fi fi-rr-settings dash-icon" aria-hidden="true"></i>
                <div class="dash-title">{{ t('customer_account.menu.settings') }}</div>
                <a href="{{ localized_route('account.settings') }}"
                   class="stretched-link"
                   aria-label="{{ t('customer_account.menu.settings') }}"></a>
            </div>
        </div>
    </div>
</div>
@endsection
