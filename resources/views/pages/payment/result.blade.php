{{-- resources/views/pages/payment/result.blade.php --}}
@extends('layouts.app')

@section('title', 'Ödeme Sonucu')

@section('content')
    @php
        use Illuminate\Support\Facades\Route;

        $oid = isset($oid) ? trim((string) $oid) : '';
        $hint = isset($hint) ? trim((string) $hint) : '';
        $checkoutCode = isset($checkoutCode) ? (string) $checkoutCode : '';

        $initialFailHint = ($hint === 'fail');

        // Status endpoint localized değil
        $statusUrl = route('payment.nestpay.status', ['oid' => $oid]);

        // Sepeti temizleme endpoint'i (CSRF'li)
        $clearCartUrl = route('cart.clear');

        // Payment sayfasına dön (tekrar ödeme akışı)
        $retryUrl = $checkoutCode !== ''
            ? localized_route('payment', ['code' => $checkoutCode])
            : localized_route('cart');

        // Sepete dön
        $cartUrl = localized_route('cart');

        // Rezervasyonlarım (route adı projede kesin değil → varsa göster)
        $hasReservationsRoute = Route::has('account.orders');
        $reservationsUrl = $hasReservationsRoute ? localized_route('account.orders') : null;
    @endphp

    <style>
        @keyframes icr-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .icr-spin { animation: icr-spin 1s linear infinite; display: inline-block; }
    </style>

    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-7 text-center">

                {{-- ICON --}}
                <div id="resultIcon"
                     class="d-inline-flex align-items-center justify-content-center rounded-circle {{ $initialFailHint ? 'bg-warning-subtle text-warning' : 'bg-primary-subtle text-primary' }}"
                     style="width: 110px; height: 110px;">
                    <i id="resultIconI"
                       class="fi {{ $initialFailHint ? 'fi-rr-info' : 'fi-rr-spinner icr-spin' }}"
                       style="font-size: 56px;"></i>
                </div>

                {{-- TITLE --}}
                <h1 id="resultTitle" class="h3 mt-4 mb-2">
                    {{ $initialFailHint ? 'İşleminiz kontrol ediliyor' : 'Ödemeniz kontrol ediliyor' }}
                </h1>

                {{-- DESC --}}
                <p id="resultDesc" class="text-muted mb-3">
                    {{ $initialFailHint
                        ? 'Bankadan gelen dönüş başarısız görünüyor. Güvenlik kontrolü için sonuç bekleniyor.'
                        : 'Lütfen bekleyin. Sonuç kesinleşince bu sayfa otomatik güncellenecek.'
                    }}
                </p>

                {{-- ALERT (dynamic) --}}
                <div id="resultAlert" class="alert {{ $initialFailHint ? 'alert-warning' : 'alert-info' }} d-inline-block text-start" role="alert">
                    <div class="fw-semibold mb-1" id="resultAlertTitle">{{ $initialFailHint ? 'Kontrol sürüyor' : 'Bekleniyor' }}</div>
                    <div id="resultAlertBody">
                        {{ $initialFailHint ? 'Sonuç teyit ediliyor…' : 'Ödeme sonucu doğrulanıyor…' }}
                    </div>
                </div>

                {{-- ACTIONS (dynamic) --}}
                <div id="resultActions" class="d-flex flex-column flex-md-row gap-2 justify-content-center mt-3">
                    <a id="btnHome" href="{{ localized_route('home') }}" class="btn btn-outline-secondary">
                        Anasayfaya Dön
                    </a>

                    {{-- Timeout state için: Yenile --}}
                    <button id="btnRefresh" type="button" class="btn btn-primary d-none">
                        Yenile
                    </button>

                    {{-- Timeout state için: Rezervasyonlarım (route varsa) --}}
                    @if ($hasReservationsRoute)
                        <a id="btnReservations" href="{{ $reservationsUrl }}" class="btn btn-outline-primary d-none">
                            Rezervasyonlarım
                        </a>
                    @endif

                    {{-- Fail state için: Sepete dön --}}
                    <a id="btnCart" href="{{ $cartUrl }}" class="btn btn-primary d-none">
                        Sepete Dön
                    </a>

                    {{-- Default: Tekrar dene (payment) --}}
                    <a id="btnRetry" href="{{ $retryUrl }}" class="btn btn-primary">
                        Tekrar Dene
                    </a>
                </div>

            </div>
        </div>
    </section>

    <script>
        (function () {
            const oid = @json($oid);
            const statusUrl = @json($statusUrl);
            const clearCartUrl = @json($clearCartUrl);
            const csrfToken = @json(csrf_token());
            const hint = @json($hint);

            if (!oid || !statusUrl) return;

            const elIcon = document.getElementById('resultIcon');
            const elIconI = document.getElementById('resultIconI');
            const elTitle = document.getElementById('resultTitle');
            const elDesc = document.getElementById('resultDesc');

            const elAlert = document.getElementById('resultAlert');
            const elAlertTitle = document.getElementById('resultAlertTitle');
            const elAlertBody = document.getElementById('resultAlertBody');

            const elBtnRetry = document.getElementById('btnRetry');
            const elBtnRefresh = document.getElementById('btnRefresh');
            const elBtnReservations = document.getElementById('btnReservations');
            const elBtnCart = document.getElementById('btnCart');

            if (hint === 'fail') {
                setFailed('İşlem banka tarafından reddedildi.');
                return;
            }

            let tries = 0;
            const intervalMs = 3000; // 3sn
            const maxTries = 40;     // 40 * 3sn = 120sn

            let clearedOnce = false;

            function sleep(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }

            function show(el) { if (el) el.classList.remove('d-none'); }
            function hide(el) { if (el) el.classList.add('d-none'); }

            async function tryClearCartWithRetry() {
                if (clearedOnce) return;
                clearedOnce = true;

                if (!clearCartUrl || !csrfToken) return;

                const delays = [400, 800, 1200];

                for (let i = 0; i < delays.length; i++) {
                    try {
                        const res = await fetch(clearCartUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'same-origin',
                            cache: 'no-store',
                            body: JSON.stringify({ reason: 'payment_success', oid: oid }),
                        });

                        if (res && res.ok) {
                            return;
                        }
                    } catch (e) {
                        // continue
                    }

                    await sleep(delays[i]);
                }
            }

            function setWaiting() {
                // spinner already on initial success-hint path; fail-hint path uses info icon initially
                if (elIconI && !elIconI.classList.contains('icr-spin')) {
                    // fail hint'te bile beklerken spinner döndürelim
                    elIconI.className = 'fi fi-rr-spinner icr-spin';
                }
            }

            function setSuccess() {
                elIcon.className = 'd-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success';
                elIconI.className = 'fi fi-rr-check';
                elTitle.textContent = 'Siparişinizi aldık';
                elDesc.textContent = 'Ödemeniz başarıyla alındı. Rezervasyonunuz işleme alındı.';

                // success'te alert göstermeyelim
                if (elAlert) elAlert.classList.add('d-none');

                // butonlar
                hide(elBtnRetry);
                hide(elBtnRefresh);
                hide(elBtnReservations);
                hide(elBtnCart);

                // sepeti temizle
                tryClearCartWithRetry();
            }

            function setFailed(message) {
                elIcon.className = 'd-inline-flex align-items-center justify-content-center rounded-circle bg-danger-subtle text-danger';
                elIconI.className = 'fi fi-rr-cross';
                elTitle.textContent = 'Ödeme başarısız';
                elDesc.textContent = 'Ödeme doğrulanamadı veya işlem iptal edildi.';

                if (elAlert) elAlert.classList.remove('d-none');
                if (elAlert) elAlert.className = 'alert alert-danger d-inline-block text-start';
                if (elAlertTitle) elAlertTitle.textContent = 'Hata';
                if (elAlertBody) elAlertBody.textContent = message || 'Ödeme doğrulanamadı.';

                // butonlar: Sepete Dön (+ Anasayfa zaten var)
                hide(elBtnRetry);
                hide(elBtnRefresh);
                hide(elBtnReservations);
                show(elBtnCart);
            }

            function setTimeoutState() {
                elIcon.className = 'd-inline-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning';
                elIconI.className = 'fi fi-rr-time-forward';
                elTitle.textContent = 'Banka onayı gecikti';
                elDesc.textContent = 'Bu sayfada kalıp yenileyebilir veya daha sonra Rezervasyonlarım sayfasından kontrol edebilirsiniz.';

                if (elAlert) elAlert.classList.remove('d-none');
                if (elAlert) elAlert.className = 'alert alert-warning d-inline-block text-start';
                if (elAlertTitle) elAlertTitle.textContent = 'Beklemede';
                if (elAlertBody) elAlertBody.textContent = 'Doğrulama cevabı henüz alınamadı.';

                // butonlar
                hide(elBtnRetry);
                hide(elBtnCart);

                show(elBtnRefresh);
                show(elBtnReservations);

                if (elBtnRefresh) {
                    elBtnRefresh.onclick = function () {
                        window.location.reload();
                    };
                }
            }

            async function poll() {
                tries++;

                try {
                    const res = await fetch(statusUrl, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                        cache: 'no-store',
                    });

                    if (res.ok) {
                        const data = await res.json();

                        if (data && data.ok && data.status) {
                            if (data.status === 'success') {
                                setSuccess();
                                return;
                            }

                            if (data.status === 'failed') {
                                const msg = data.message || null;
                                setFailed(msg);
                                return;
                            }
                        }
                    }
                } catch (e) {
                    // continue
                }

                if (tries >= maxTries) {
                    setTimeoutState();
                    return;
                }

                setWaiting();
                setTimeout(poll, intervalMs);
            }

            setTimeout(poll, 800);
        })();
    </script>
@endsection
