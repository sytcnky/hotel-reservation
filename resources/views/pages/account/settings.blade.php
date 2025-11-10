{{-- resources/views/pages/account/settings.blade.php --}}
@extends('layouts.account')

@section('account_content')

@php
use Illuminate\Support\Str;
$u = auth()->user();

// Global alertler
$status = session('status');

// Ad-soyad için görsel fallback (DB'ye yazmaz)
$first = old('first_name', $u->first_name ?? '');
$last  = old('last_name',  $u->last_name  ?? '');

if ($first === '' && !empty($u->name)) {
$first = trim(Str::beforeLast($u->name, ' '));
if ($first === '') $first = $u->name; // tek kelime isim
}
if ($last === '' && !empty($u->name)) {
$last = trim(Str::afterLast($u->name, ' '));
if ($last === $u->name) $last = ''; // tek kelime isimde soyadı boş bırak
}

// Şifre bölümünün açılış durumu
$pwdOpen = $status === 'password-updated'
|| $errors->hasAny(['current_password','password','password_confirmation']);
@endphp

{{-- Üst global uyarılar --}}
@if ($status === 'profile-updated')
<div class="alert alert-success">Bilgileriniz güncellendi.</div>
@endif
@if ($status === 'password-updated')
<div class="alert alert-success">Şifreniz güncellendi.</div>
@endif

{{-- ÜYELİK BİLGİLERİM --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h2 class="h6 mb-3">Üyelik Bilgilerim</h2>

        <form action="{{ route('account.settings.update') }}" method="post" class="row g-3" novalidate>
            @csrf
            @method('PUT')

            <div class="col-md-6">
                <label for="firstName" class="form-label">Ad</label>
                <input
                    type="text"
                    id="firstName"
                    name="first_name"
                    value="{{ $first }}"
                    class="form-control @error('first_name') is-invalid @enderror"
                    placeholder="Adınız"
                    required
                >
                @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="lastName" class="form-label">Soyad</label>
                <input
                    type="text"
                    id="lastName"
                    name="last_name"
                    value="{{ $last }}"
                    class="form-control @error('last_name') is-invalid @enderror"
                    placeholder="Soyadınız"
                    required
                >
                @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6" data-phone-container>
                <label for="phone" class="form-label">Telefon</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="{{ old('phone', auth()->user()->phone) }}"
                    class="form-control @error('phone') is-invalid @enderror"
                    data-phone-input
                    required
                >

                <div class="invalid-feedback d-block phone-error"
                     @if($errors->has('phone')) style="display:block" @else style="display:none" @endif>
                    @error('phone'){{ $message }}@enderror
                </div>
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">E-posta</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $u->email) }}"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="ornek@eposta.com"
                    required
                >
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
</div>

{{-- ŞİFRE DEĞİŞTİR --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h2 class="h6 mb-3">Şifre Değiştir</h2>

        {{-- Görsel satır --}}
        <div id="pwdDisplayRow" class="row g-2 align-items-center {{ $pwdOpen ? 'd-none' : '' }}">
            <div class="col-md-6">
                <input type="password"
                       id="currentPasswordDisplay"
                       class="form-control"
                       placeholder="••••••••"
                       disabled
                       autocomplete="current-password">
            </div>
            <div class="col-auto">
                <button type="button" id="btnStartChange" class="btn btn-link p-0">Değiştir</button>
            </div>
        </div>

        {{-- Gerçek form --}}
        <form id="passwordForm"
              action="{{ route('account.password.update') }}"
              method="post"
              class="mt-3 {{ $pwdOpen ? '' : 'd-none' }}"
              novalidate>
            @csrf
            @method('PUT')

            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="current_password" class="form-label">Mevcut Şifre</label>
                    <input type="password" name="current_password" id="current_password"
                           class="form-control @error('current_password') is-invalid @enderror"
                           autocomplete="current-password" required>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <label for="newPassword" class="form-label">Yeni Şifre</label>
                    <input type="password" name="password" id="newPassword"
                           class="form-control @error('password') is-invalid @enderror"
                           autocomplete="new-password" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6">
                    <label for="newPasswordConfirm" class="form-label">Yeni Şifre (Tekrar)</label>
                    <input type="password" name="password_confirmation" id="newPasswordConfirm"
                           class="form-control" autocomplete="new-password" required>
                </div>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="button" id="btnCancelChange" class="btn btn-outline-secondary">İptal</button>
                <button type="submit" class="btn btn-primary">Şifremi Değiştir</button>
            </div>
        </form>
    </div>
</div>

{{-- BAĞLI HESAPLAR / BİLDİRİMLER --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h2 class="h6 mb-3">Bağlı Hesaplar</h2>
        <div class="vstack gap-3">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="fw-semibold">Google</div>
                    <span class="badge bg-secondary">Bağlı değil</span>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm">Bağla</button>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="fw-semibold">Facebook</div>
                    <span class="badge bg-success">Bağlı</span>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm">Kaldır</button>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="#" method="post" class="vstack gap-2">
            <h2 class="h6 mb-3">E-Posta Ayarlarım</h2>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="swMarketing">
                <label class="form-check-label" for="swMarketing">Kampanyalar ve fırsatlar</label>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Kaydet</button>
            </div>
        </form>
    </div>
</div>

{{-- Basit JS: Şifre alanı davranışı --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const display  = document.getElementById('pwdDisplayRow');
        const form     = document.getElementById('passwordForm');
        const btnStart = document.getElementById('btnStartChange');
        const btnCancel= document.getElementById('btnCancelChange');
        const current  = document.getElementById('current_password');

        btnStart?.addEventListener('click', () => {
            display.classList.add('d-none');
            form.classList.remove('d-none');
            current?.focus();
        });

        btnCancel?.addEventListener('click', () => {
            form.reset();
            form.classList.add('d-none');
            display.classList.remove('d-none');
        });
    });
</script>
@endsection
