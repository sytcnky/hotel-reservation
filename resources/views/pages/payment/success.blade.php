{{-- resources/views/pages/payment/success.blade.php --}}
@extends('layouts.app')

@section('title', 'Rezervasyon Tamamlandı')

@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-7 text-center">
            <!-- Büyük onay ikonu -->
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success"
                 style="width: 110px; height: 110px;">
                <i class="fi fi-rr-check" style="font-size: 56px;"></i>
            </div>

            <h1 class="h3 mt-4 mb-2">Rezervasyonunuz tamamlandı</h1>
            <p class="text-muted mb-3">
                Ödemeniz başarıyla alındı. Onay e-postası adresinize gönderildi.
            </p>

            {{-- (Opsiyonel) Rezervasyon No badge’i – backend bağlanınca doldur --}}
            @isset($reservationCode)
            <div class="badge bg-light text-dark border fw-semibold px-3 py-2 mb-3">
                Rezervasyon No: <span class="text-primary">{{ $reservationCode }}</span>
            </div>
            @endisset

            <div class="d-flex flex-column flex-md-row gap-2 justify-content-center mt-2">
                <a href="{{ localized_route('home') }}" class="btn btn-primary">
                    Anasayfaya Dön
                </a>
                <a href="{{ localized_route('account.bookings') }}" class="btn btn-outline-secondary">
                    Rezervasyonlarım
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
