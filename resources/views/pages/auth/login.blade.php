{{-- resources/views/pages/auth/login.blade.php --}}
@extends('layouts.app')

@section('title', 'Giriş / Misafir')

@section('content')
@php
$redirect  = request('redirect', '/payment');
$fromCart  = request()->boolean('from_cart');   // sepetten mi?
$prefGuest = request()->boolean('guest');       // misafir toggle başlangıç
@endphp

<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-5">

            <h1 class="mb-4 text-secondary">Giriş Yap</h1>

            <p>Hesabın yok mu? Hemen <a href="{{ route('register') }}" class="text-primary text-decoration-none">Kayıt Ol</a></p>

            <!-- LOGIN (email + şifre) -->
            <div id="loginFields">
                <form method="POST" action="{{ route('login.store') }}" class="needs-validation" novalidate>
                    @csrf
                    <input type="hidden" name="redirect" value="{{ $redirect }}">

                    @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label">E-posta</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            placeholder="ornek@mail.com"
                            required
                        >
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Şifre</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="••••••••"
                            required
                        >
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Beni hatırla</label>
                        </div>
                        <a href="{{ route('password.request') }}" class="small">Şifremi unuttum</a>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Devam Et</button>

                    <!-- Sosyal giriş (ileride eklenecek) -->
                    <div class="text-center text-muted small my-3">veya</div>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <a href="#" class="btn btn-danger w-100 d-flex justify-content-center align-items-center">
                                <i class="fi fi-brands-google me-2 fs-5"></i> <small>Google ile devam et</small>
                            </a>
                        </div>
                        <div class="col-12 col-md-6">
                            <a href="#" class="btn btn-primary w-100 d-flex justify-content-center align-items-center">
                                <i class="fi fi-brands-facebook me-2 fs-5"></i> <small>Facebook ile devam et</small>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- GUEST (ad, soyad, e-posta, telefon) -->
            <div id="guestFields" class="d-none">
                <form action="#" method="POST" class="needs-validation" novalidate>
                    <input type="hidden" name="redirect" value="{{ $redirect }}">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label">Ad</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">Soyad</label>
                            <input type="text" class="form-control" required>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">E-posta</label>
                        <input type="email" class="form-control" placeholder="ornek@mail.com" required>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Telefon</label>
                        <input type="tel" class="form-control" placeholder="+90 5xx xxx xx xx" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 mt-3">
                        Devam Et
                    </button>
                </form>
            </div>

            <!-- Üye olmadan devam et -->
            @if($fromCart)
            <div class="form-check form-switch mt-4">
                <input class="form-check-input" type="checkbox" id="guestCheckout" @checked($prefGuest)>
                <label class="form-check-label" for="guestCheckout">Üye olmadan devam et</label>
                <p class="text-muted small mt-2 mb-0">
                    Hesap oluşturmak zorunda değilsiniz. Bilgileriniz sadece bu rezervasyon için kullanılacaktır.
                </p>
            </div>
            @endif

            <div class="text-muted small mt-4">
                Devam ederek KVKK ve ön bilgilendirme metinlerini okuduğunuzu onaylamış olursunuz.
            </div>
        </div>
    </div>
</section>

{{-- Basit toggle --}}
<script>
    document.addEventListener('DOMContentLoaded', function(){
        const chk   = document.getElementById('guestCheckout');
        const login = document.getElementById('loginFields');
        const guest = document.getElementById('guestFields');
        if (!login || !guest) return;

        function sync() {
            if (chk && chk.checked) { login.classList.add('d-none'); guest.classList.remove('d-none'); }
            else { guest.classList.add('d-none'); login.classList.remove('d-none'); }
        }
        if (chk) chk.addEventListener('change', sync);
        sync();
    });
</script>
@endsection
