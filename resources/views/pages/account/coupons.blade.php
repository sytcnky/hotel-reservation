@extends('layouts.account')

@section('account_content')

<div class="row">
    <div class="col-12">
        <h4 class="mb-3 h6">Aktif Kuponlarım</h4>
    </div>
    <div class="col-12 col-lg-6 mb-4">
        <!-- Kupon 1 -->
        <div class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
            <div class="coupon-badge border me-2 text-center">
                <div class="h2 fw-bolder text-primary mb-0">%5</div>
                <div class="badge text-bg-primary">İNDİRİM</div>
            </div>
            <div class="flex-grow-1">
                <div class="small fw-semibold">İlk rezervasyonunuza %5 indirim!</div>
                <div class="small text-muted">Alt limit: Yok</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6 mb-4">
        <!-- Kupon 2 -->
        <div class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
            <div class="coupon-badge border me-2 text-center">
                <div class="h2 fw-bolder text-primary mb-0">7=6</div>
                <div class="badge text-bg-primary">GECE</div>
            </div>
            <div class="flex-grow-1">
                <div class="small fw-semibold">7 Gece Kal, 6 Gece Öde!</div>
                <div class="small text-muted">Alt limit: Yok</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6 mb-4">
        <!-- Kupon 3 -->
        <div class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
            <div class="coupon-badge border me-2 text-center">
                <div class="h2 fw-bolder text-primary mb-0">%10</div>
                <div class="badge text-bg-primary">İNDİRİM</div>
            </div>
            <div class="flex-grow-1">
                <div class="small fw-semibold">Hafta içi rezervasyonlarına ekstra %10 indirim</div>
                <div class="small text-muted">Alt limit: 2 Gece</div>
            </div>
        </div>
    </div>
</div>
<div class="row mt-5">
    <div class="col-12">
        <h4 class="mb-3 h6">Geçmiş Kuponlarım</h4>
    </div>
    <div class="col-12 col-lg-6">
        <!-- Kupon 4 -->
        <div class="coupon-card mb-3 border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
            <div class="coupon-badge border me-2 text-center">
                <div class="h2 fw-bolder text-primary mb-0">%20</div>
                <div class="badge text-bg-primary">ÖZEL</div>
            </div>
            <div class="flex-grow-1">
                <div class="small fw-semibold">Erken rezervasyona özel %20 indirim</div>
                <div class="small text-muted">Alt limit: 5 Gece</div>
            </div>
        </div>
    </div>
</div>
@endsection
