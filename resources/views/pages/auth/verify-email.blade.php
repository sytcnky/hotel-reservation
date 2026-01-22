@extends('layouts.app')

@section('title', t('auth.email_verify.title'))

@section('content')
    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-xl-5">
                <h1 class="mb-4 text-secondary">{{ t('auth.email_verify.heading') }}</h1>

                @if (session('status') === 'verification-link-sent')
                    <div class="alert alert-success">
                        {{ t('auth.email_verify.link_sent') }}
                    </div>
                @endif

                @if ($errors->has('verify'))
                    <div class="alert alert-danger" id="verifyRateLimitAlert"
                         @if(session('verify_retry')) data-seconds="{{ (int) session('verify_retry') }}" @endif>
                        {{ $errors->first('verify') }}

                        @if(session('verify_retry'))
                            <div class="mt-2">
                                <small>
                                    <span id="verifyCountdown">{{ (int) session('verify_retry') }}</span>
                                    {{ t('auth.email_verify.retry_suffix') }}
                                </small>
                            </div>
                        @endif
                    </div>
                @endif

                <p>{{ t('auth.email_verify.instructions') }}</p>

                <form method="POST" action="{{ route('verification.send') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-success w-100">
                        {{ t('auth.email_verify.actions.resend') }}
                    </button>
                </form>

                <form id="logout-form" method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-link text-muted w-100">
                        {{ t('auth.email_verify.actions.logout') }}
                    </button>
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

            // sadece "Bağlantıyı Tekrar Gönder" butonunu disable et
            const resendBtn = document.querySelector('form[action="{{ route('verification.send') }}"] button[type="submit"]');

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
