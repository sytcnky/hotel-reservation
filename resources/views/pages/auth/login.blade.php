{{-- resources/views/pages/auth/login.blade.php --}}
@extends('layouts.app')

@section('title', t('auth.login.title'))

@section('content')
    @php
        $redirect  = request('redirect', '/payment');
        $fromCart  = request()->boolean('from_cart');   // sepetten mi?
        $prefGuest = request()->boolean('guest');       // misafir toggle başlangıç
    @endphp

    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-5">

                <h1 class="mb-4 text-secondary">{{ t('auth.login.heading') }}</h1>

                <p>
                    {{ t('auth.login.no_account') }}
                    <a href="{{ route('register') }}" class="text-primary text-decoration-none">
                        {{ t('auth.login.register_link') }}
                    </a>
                </p>

                <!-- LOGIN (email + şifre) -->
                <div id="loginFields">
                    <form method="POST" action="{{ route('login.store') }}" class="needs-validation" novalidate>
                        @csrf
                        <input type="hidden" name="redirect" value="{{ $redirect }}">

                        @if (session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label">{{ t('auth.login.email.label') }}</label>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror"
                                placeholder="{{ t('auth.login.email.placeholder') }}"
                                required
                            >
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ t('auth.login.password.label') }}</label>
                            <input
                                type="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="{{ t('auth.login.password.placeholder') }}"
                                required
                            >
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                                <label class="form-check-label" for="rememberMe">{{ t('auth.login.remember_me') }}</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="small">{{ t('auth.login.forgot_password') }}</a>
                        </div>

                        <button type="submit" class="btn btn-success w-100">{{ t('auth.login.actions.continue') }}</button>

                        <!-- Sosyal giriş (ileride eklenecek) -->
                        <div class="text-center text-muted small my-3">{{ t('auth.login.or') }}</div>
                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <a href="#" class="btn btn-danger w-100 d-flex justify-content-center align-items-center">
                                    <i class="fi fi-brands-google me-2 fs-5"></i>
                                    <small>{{ t('auth.login.social.google') }}</small>
                                </a>
                            </div>
                            <div class="col-12 col-md-6">
                                <a href="#" class="btn btn-primary w-100 d-flex justify-content-center align-items-center">
                                    <i class="fi fi-brands-facebook me-2 fs-5"></i>
                                    <small>{{ t('auth.login.social.facebook') }}</small>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- GUEST (ad, soyad, e-posta, telefon) --}}
                <div id="guestFields" class="d-none">
                    <form action="{{ route('checkout.start.guest') }}"
                          method="POST"
                          class="needs-validation"
                          novalidate>
                        @csrf

                        {{-- Şimdilik redirect’i tutuyoruz, gerekirse ileride kullanırız --}}
                        <input type="hidden" name="redirect" value="{{ request('redirect') }}">

                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <label class="form-label">{{ t('auth.login.guest.first_name.label') }}</label>
                                <input type="text"
                                       name="guest_first_name"
                                       class="form-control"
                                       required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">{{ t('auth.login.guest.last_name.label') }}</label>
                                <input type="text"
                                       name="guest_last_name"
                                       class="form-control"
                                       required>
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="form-label">{{ t('auth.login.guest.email.label') }}</label>
                            <input type="email"
                                   name="guest_email"
                                   class="form-control"
                                   placeholder="{{ t('auth.login.email.placeholder') }}"
                                   required>
                        </div>

                        <div class="mt-3">
                            <div class="mt-3">
                                <label class="form-label">{{ t('auth.login.guest.phone.label') }}</label>

                                <input
                                    type="tel"
                                    id="guest_phone"
                                    name="guest_phone"
                                    value="{{ old('guest_phone') }}"
                                    class="form-control @error('guest_phone') is-invalid @enderror"
                                    placeholder="{{ t('auth.login.guest.phone.placeholder') }}"
                                    data-phone-input
                                    required
                                >

                                <div class="invalid-feedback d-block phone-error"
                                     @if($errors->has('guest_phone')) style="display:block" @else style="display:none" @endif>
                                    @error('guest_phone'){{ $message }}@enderror
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 mt-3">
                            {{ t('auth.login.actions.continue') }}
                        </button>
                    </form>
                </div>

                <!-- Üye olmadan devam et -->
                @if($fromCart)
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" id="guestCheckout" @checked($prefGuest)>
                        <label class="form-check-label" for="guestCheckout">{{ t('auth.login.guest.toggle') }}</label>
                        <p class="text-muted small mt-2 mb-0">
                            {{ t('auth.login.guest.note') }}
                        </p>
                    </div>
                @endif

                <div class="text-muted small mt-4">
                    {{ t('auth.login.footer_notice') }}
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

        window.intlTelInputI18n = {
            searchPlaceholder: "{{ e(t('intl_tel.search_placeholder')) }}",
            zeroSearchResults: "{{ e(t('intl_tel.zero_results')) }}",
            clearSearch: "{{ e(t('intl_tel.clear_search')) }}",
        };
        window.phone_required = "{{ e(t('intl_tel.phone_required')) }}";
        window.phone_invalid  = "{{ e(t('intl_tel.phone_invalid')) }}";
    </script>
@endsection
