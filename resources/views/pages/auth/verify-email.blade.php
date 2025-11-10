@extends('layouts.app')

@section('title', 'E-posta Doğrulaması')

@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-5">
            <h1 class="mb-4 text-secondary">E-posta Doğrulaması Gerekli</h1>

            @if (session('status') === 'verification-link-sent')
            <div class="alert alert-success">Doğrulama bağlantısı e-postana gönderildi.</div>
            @endif

            <p>Hesabını tamamlamak için e-postana gelen doğrulama bağlantısına tıkla.</p>

            <form method="POST" action="{{ route('verification.send') }}" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-success w-100">Bağlantıyı Tekrar Gönder</button>
            </form>

            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-link text-muted w-100">Çıkış yap</button>
            </form>
        </div>
    </div>
</section>
@endsection
