@extends('layouts.app')

@section('title', 'Şifre Yenile')

@section('content')
@php($email = old('email', $email ?? ''))
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-5">
            <h1 class="mb-4 text-secondary">Şifre Yenile</h1>

            @if ($errors->has('email'))
                <div class="alert alert-danger" id="pwResetRateLimitAlert"
                     @if(session('pw_reset_retry')) data-seconds="{{ (int) session('pw_reset_retry') }}" @endif>
                    {{ $errors->first('email') }}

                    @if(session('pw_reset_retry'))
                        <div class="mt-2">
                            <small>
                                <span id="pwResetCountdown">{{ (int) session('pw_reset_retry') }}</span> saniye sonra tekrar deneyebilirsin.
                            </small>
                        </div>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="needs-validation" novalidate>
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label class="form-label">E-posta</label>
                    <input type="email" name="email" value="{{ $email }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Yeni Şifre</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label">Yeni Şifre (Tekrar)</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success w-100">Şifreyi Güncelle</button>
            </form>
        </div>
    </div>
</section>
@endsection
