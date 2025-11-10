{{-- resources/views/pages/account/index.blade.php --}}
@extends('layouts.account')

@section('account_content')

<style>
    .dash-card .card-body{
        text-align:center; padding:24px; border-radius:.75rem; cursor:pointer;
    }
    .dash-card:hover .card-body,
    .dash-card:focus-within .card-body{
        background: rgba(0,0,0,.03);
    }
    .dash-icon{
        font-size:44px; line-height:1; display:block;
    }
    .dash-title{ margin-top:.75rem; font-weight:600; }
    .corner-badge{ position:absolute; top:.5rem; right:.5rem; }
</style>
<section>
    <div class="my-4">
        <h1 class="display-5 fw-bold text-secondary">Merhaba Ayşe,</h1>
        <p class="lead text-muted">Hesabınızla ilgili ayarları, geçmiş rezervasyonlarınızı ve destek taleplerinizi burada bulacaksınız.</p>
    </div>
</section>
<div class="row g-3 row-cols-1 row-cols-sm-2">
    {{-- Rezervasyonlarım --}}
    <div class="col">
        <div class="card shadow-sm position-relative dash-card">
            <div class="card-body text-secondary">
                <i class="fi fi-rr-calendar dash-icon" aria-hidden="true"></i>
                <div class="dash-title">Rezervasyonlarım</div>
                <a href="{{ route('account.bookings') }}" class="stretched-link" aria-label="Rezervasyonlarım"></a>
            </div>
        </div>
    </div>

    {{-- İndirim Kuponlarım --}}
    <div class="col">
        <div class="card shadow-sm position-relative dash-card">
            <div class="card-body text-secondary">
                <i class="fi fi-rr-ticket dash-icon" aria-hidden="true"></i>
                <div class="dash-title">İndirim Kuponlarım</div>
                <a href="{{ route('account.coupons') }}" class="stretched-link" aria-label="İndirim Kuponlarım"></a>
            </div>
        </div>
    </div>

    {{-- Destek Taleplerim (köşe badge = 1) --}}
    <div class="col">
        <div class="card shadow-sm position-relative dash-card">
            <span class="badge bg-danger corner-badge">1</span>
            <div class="card-body text-secondary">
                <i class="fi fi-rr-life-ring dash-icon" aria-hidden="true"></i>
                <div class="dash-title">Destek Taleplerim</div>
                <a href="{{ route('account.tickets') }}" class="stretched-link" aria-label="Destek Taleplerim"></a>
            </div>
        </div>
    </div>

    {{-- Üyelik Ayarlarım --}}
    <div class="col">
        <div class="card shadow-sm position-relative dash-card">
            <div class="card-body text-secondary">
                <i class="fi fi-rr-settings dash-icon" aria-hidden="true"></i>
                <div class="dash-title">Üyelik Ayarlarım</div>
                <a href="{{ route('account.settings') }}" class="stretched-link" aria-label="Üyelik Ayarlarım"></a>
            </div>
        </div>
    </div>
</div>

@endsection
