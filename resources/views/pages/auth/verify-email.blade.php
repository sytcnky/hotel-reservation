@extends('layouts.app')

@section('title', 'E-posta Doğrulaması')

@section('content')
<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-5">
            <h1 class="mb-4 text-secondary">E-posta Doğrulaması Gerekli</h1>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success">
                    Doğrulama bağlantısı e-postana gönderildi.
                </div>
            @endif

            @if ($errors->has('verify'))
                <div class="alert alert-danger" id="verifyRateLimitAlert"
                     @if(session('verify_retry')) data-seconds="{{ (int) session('verify_retry') }}" @endif>
                    {{ $errors->first('verify') }}
                    @if(session('verify_retry'))
                        <div class="mt-2">
                            <small>
                                <span id="verifyCountdown">{{ (int) session('verify_retry') }}</span> saniye sonra tekrar deneyebilirsin.
                            </small>
                        </div>
                    @endif
                </div>
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
<script>
    (function () {
        const alertEl = document.getElementById('verifyRateLimitAlert');
        if (!alertEl) return;

        const secondsAttr = alertEl.getAttribute('data-seconds');
        if (!secondsAttr) return;

        let remaining = parseInt(secondsAttr, 10);
        if (!Number.isFinite(remaining) || remaining <= 0) return;

        const countdownEl = document.getElementById('verifyCountdown');
        const resendBtn = document.querySelector('[type="submit"]'); // verify sayfandaki submit butonu

        if (resendBtn) resendBtn.disabled = true;

        const tick = () => {
            remaining -= 1;

            if (countdownEl) countdownEl.textContent = String(Math.max(remaining, 0));

            if (remaining <= 0) {
                if (resendBtn) resendBtn.disabled = false;
                alertEl.remove();
                clearInterval(timer);
            }
        };

        const timer = setInterval(tick, 1000);
    })();
</script>
@endsection
