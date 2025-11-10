@extends('layouts.app')

@section('title', 'Şifremi Unuttum')

@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-5">
            <h1 class="mb-4 text-secondary">Şifremi Unuttum</h1>

            @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="needs-validation" novalidate>
                @csrf

                <div class="mb-3">
                    <label class="form-label">E-posta</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" required>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn btn-success w-100">Sıfırlama Linki Gönder</button>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}">Geri dön</a>
                </div>
            </form>
        </div>
    </div>
</section>
@endsection
