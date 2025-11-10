@extends('layouts.app')

@section('title', 'Kayıt Ol')

@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-5">
            <h1 class="mb-4 text-secondary">Kayıt Ol</h1>

            <form method="POST" action="{{ route('register.store') }}" class="needs-validation" novalidate>
                @csrf

                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label">Ad</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}"
                               class="form-control @error('first_name') is-invalid @enderror" required>
                        @error('first_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Soyad</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}"
                               class="form-control @error('last_name') is-invalid @enderror" required>
                        @error('last_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="mb-3 mt-2">
                    <label class="form-label">Telefon (opsiyonel)</label>
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

                <div class="mb-3">
                    <label class="form-label">E-posta</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Şifre</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Şifre (Tekrar)</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100">Kayıt Ol</button>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}">Zaten hesabın var mı? Giriş yap</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
