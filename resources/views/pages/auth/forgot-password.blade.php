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

            @if (session('pw_reset_retry'))
                <div class="alert alert-danger" id="pwResetRateLimitAlert"
                     data-seconds="{{ (int) session('pw_reset_retry') }}">
                    {{ $errors->first('email') }}

                    <div class="mt-2">
                        <small>
                            <span id="pwResetCountdown">{{ (int) session('pw_reset_retry') }}</span>
                            saniye sonra tekrar deneyebilirsin.
                        </small>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="needs-validation" novalidate>
                @csrf

                <div class="mb-3">
                    <label class="form-label">E-posta</label>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        required
                    >

                    @error('email')
                    @if (! session()->has('pw_reset_retry'))
                        <div class="invalid-feedback">{{ $message }}</div>
                    @endif
                    @enderror
                </div>

                <button id="pwResetSubmitBtn" class="btn btn-success w-100" type="submit">Sıfırlama Linki Gönder</button>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}">Geri dön</a>
                </div>
            </form>
        </div>
    </div>
</section>
<script>
    (function () {
        const alertEl = document.getElementById('pwResetRateLimitAlert');
        if (!alertEl) return;

        const secondsAttr = alertEl.getAttribute('data-seconds');
        if (!secondsAttr) return;

        let remaining = parseInt(secondsAttr, 10);
        if (!Number.isFinite(remaining) || remaining <= 0) return;

        const countdownEl = document.getElementById('pwResetCountdown');
        const submitBtn = document.getElementById('pwResetSubmitBtn');

        if (submitBtn) submitBtn.disabled = true;

        const tick = () => {
            remaining -= 1;

            if (countdownEl) countdownEl.textContent = String(Math.max(remaining, 0));

            if (remaining <= 0) {
                if (submitBtn) submitBtn.disabled = false;
                alertEl.remove();
                clearInterval(timer);
            }
        };

        const timer = setInterval(tick, 1000);
    })();
</script>
@endsection
