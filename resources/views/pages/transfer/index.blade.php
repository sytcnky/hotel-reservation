@extends('layouts.app')

@section('title', 'Transferler')

@section('content')

<section>
    <div class="container mt-3" style="font-size: 14px">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ localized_route('home') }}">
                        <i class="fi fi-ss-house-chimney" style="vertical-align: middle"></i>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Transferler</li>
            </ol>
        </nav>
    </div>
    <div class="text-center my-5 px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-secondary">Yolculuğunuzun en kolay kısmı burası</h1>
        <p class="lead text-muted px-lg-5">
            Ulaşımınızı şansa bırakmayın. Tatil başlangıcınızdan dönüşünüze kadar
            konforlu ve güvenilir transfer hizmetleriyle yanınızdayız.
        </p>
    </div>
</section>

<section class="container pb-5">
    <div class="bg-white p-4 rounded shadow-lg">
        {{-- Arama Formu --}}
        <form class="row g-3 needs-validation" action="{{ localized_route('transfers') }}" method="GET" novalidate>
            {{-- Yön Seçimi --}}
            <div class="col-12 text-center mb-2" style="margin-top: -25px">
                <div class="btn-group bg-white shadow-sm" role="group" aria-label="Yön Seçimi">
                    <input type="radio"
                           class="btn-check"
                           name="direction"
                           id="oneway"
                           value="oneway"
                           @checked(request('direction', 'oneway') === 'oneway')
                    required>
                    <label class="btn btn-outline-primary" for="oneway">Tek Yön</label>

                    <input type="radio"
                           class="btn-check"
                           name="direction"
                           id="roundtrip"
                           value="roundtrip"
                           @checked(request('direction') === 'roundtrip')
                    required>
                    <label class="btn btn-outline-primary" for="roundtrip">Gidiş - Dönüş</label>
                </div>
            </div>

            {{-- Nereden --}}
            <div class="col-lg-2">
                <label for="from_location_id" class="form-label">Nereden</label>
                <div class="input-group has-validation">
                    <select class="form-select" id="from_location_id" name="from_location_id" required>
                        <option value="">Seçiniz</option>
                        @foreach($locations as $location)
                        <option value="{{ $location['id'] }}"
                                @selected((int) request('from_location_id') === $location['id'])>
                        {{ $location['label'] }}
                        </option>
                        @endforeach
                    </select>
                    <span class="input-group-text bg-white">
                        <i class="fi fi-rr-marker"></i>
                    </span>
                </div>
            </div>

            {{-- Nereye --}}
            <div class="col-lg-2">
                <label for="to_location_id" class="form-label">Nereye</label>
                <div class="input-group has-validation">
                    <select class="form-select" id="to_location_id" name="to_location_id" required>
                        <option value="">Seçiniz</option>
                        @foreach($locations as $location)
                        <option value="{{ $location['id'] }}"
                                @selected((int) request('to_location_id') === $location['id'])>
                        {{ $location['label'] }}
                        </option>
                        @endforeach
                    </select>
                    <span class="input-group-text bg-white">
                        <i class="fi fi-rr-marker"></i>
                    </span>
                </div>
            </div>

            {{-- Gidiş & Dönüş Tarihi --}}
            <div class="col-lg-4">
                <div class="row g-3">
                    {{-- Gidiş --}}
                    <div class="col">
                        <label for="departure_date" class="form-label">Gidiş Tarihi</label>
                        <div class="input-group has-validation">
                            <input type="text"
                                   class="form-control date-input"
                                   placeholder="gg.aa.yyyy"
                                   id="departure_date"
                                   name="departure_date"
                                   value="{{ request('departure_date') }}"
                                   required>
                            <span class="input-group-text bg-white">
                                <i class="fi fi-rr-calendar"></i>
                            </span>
                        </div>
                    </div>

                    {{-- Dönüş --}}
                    <div class="col" id="returnDateWrapper">
                        <label for="return_date" class="form-label">Dönüş Tarihi</label>
                        <div class="input-group has-validation">
                            <input type="text"
                                   class="form-control date-input"
                                   placeholder="gg.aa.yyyy"
                                   id="return_date"
                                   name="return_date"
                                   value="{{ request('return_date') }}">
                            <span class="input-group-text bg-white">
                                <i class="fi fi-rr-calendar"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kişi Sayısı --}}
            <div class="col-lg-3">
                <label for="guestInput" class="form-label">Kişi Sayısı</label>

                @php
                $giAdults = max(0, (int) request('adults', 2));
                $giChildren = max(0, (int) request('children', 0));
                $giInfants = max(0, (int) request('infants', 0));
                $giTotal = $giAdults + $giChildren + $giInfants;
                $giText = $giTotal > 0
                ? ($giAdults . ' Yetişkin'
                . ($giChildren ? ', ' . $giChildren . ' Çocuk' : '')
                . ($giInfants ? ', ' . $giInfants . ' Bebek' : '')
                )
                : '';
                @endphp

                <div class="guest-picker-wrapper position-relative">
                    <div class="input-group has-validation">
                        <input type="text"
                               id="guestInput"
                               class="form-control guest-wrapper"
                               placeholder="Kişi sayısı seçin"
                               value="{{ $giText }}"
                               readonly>
                        <span class="input-group-text bg-white">
                            <i class="fi fi-rr-user"></i>
                        </span>
                    </div>

                    <div class="guest-dropdown border rounded shadow-sm bg-white p-3 position-absolute w-100"
                         style="z-index: 10; top: 100%; display: none;">

                        {{-- Yetişkin --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Yetişkin</span>
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <button type="button"
                                        class="btn btn-outline-secondary minus"
                                        data-type="adult">−</button>
                                <input type="text"
                                       class="form-control text-center"
                                       data-type="adult"
                                       name="adults"
                                       value="{{ $giAdults }}"
                                       readonly>
                                <button type="button"
                                        class="btn btn-outline-secondary plus"
                                        data-type="adult">+</button>
                            </div>
                        </div>

                        {{-- Çocuk --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Çocuk</span>
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <button type="button"
                                        class="btn btn-outline-secondary minus"
                                        data-type="child">−</button>
                                <input type="text"
                                       class="form-control text-center"
                                       data-type="child"
                                       name="children"
                                       value="{{ $giChildren }}"
                                       readonly>
                                <button type="button"
                                        class="btn btn-outline-secondary plus"
                                        data-type="child">+</button>
                            </div>
                        </div>

                        {{-- Bebek --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Bebek</span>
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <button type="button"
                                        class="btn btn-outline-secondary minus"
                                        data-type="infant">−</button>
                                <input type="text"
                                       class="form-control text-center"
                                       data-type="infant"
                                       name="infants"
                                       value="{{ $giInfants }}"
                                       readonly>
                                <button type="button"
                                        class="btn btn-outline-secondary plus"
                                        data-type="infant">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ara --}}
            <div class="col-lg-1 d-grid align-self-end">
                <button type="submit" class="btn btn-primary d-block" title="Transferleri Göster">
                    <i class="fi fi-rr-search"></i>
                </button>
            </div>
        </form>
    </div>

    {{-- Teklif Kartı: sadece uygun teklif varsa --}}
    @if(!empty($transferOffer))
    @php
    $fromLabel = collect($locations)->firstWhere('id', $transferOffer['from_location_id'])['label'] ?? '';
    $toLabel = collect($locations)->firstWhere('id', $transferOffer['to_location_id'])['label'] ?? '';
    @endphp

    <div class="bg-white p-4 rounded shadow-sm mb-4 mt-3">
        <div class="row">
            {{-- Sol: Galeri (şimdilik örnek) --}}
            <div class="col-lg-5 pe-lg-5 mb-4">
                <div class="gallery">
                    <h4 class="fw-bold">{{ $transferOffer['vehicle_name'] }}</h4>
                    <p class="small text-muted">
                        Konforlu ve geniş araçlarımızla havalimanından konaklama noktanıza güvenli transfer.
                    </p>

                    <div class="main-gallery position-relative bg-black d-flex align-items-center justify-content-center rounded mb-3"
                         style="height: 260px;">
                        <img src="/images/samples/arac-1.png"
                             class="w-100 h-100"
                             style="object-fit: contain;"
                             alt="Araç Görseli">
                    </div>

                    <div class="d-flex gap-2 overflow-auto thumbnail-scroll">
                        @foreach(['/images/samples/arac-1.png','/images/samples/arac-2.png','/images/samples/arac-3.png','/images/samples/arac-4.png'] as $image)
                        <div class="flex-shrink-0 rounded overflow-hidden bg-black"
                             style="width: 72px; height: 72px; cursor: pointer;">
                            <img src="{{ $image }}"
                                 class="w-100 h-100"
                                 style="object-fit: cover;"
                                 alt="Araç Görseli">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Sağ: Özet + Rezervasyon Formu (booking akışı sonra bağlanacak) --}}
            <div class="col-lg-7 d-flex flex-column justify-content-between">
                <div>
                    <div class="row g-3">
                        {{-- Rota --}}
                        <div class="col">
                            <div class="border rounded p-3">
                                <div class="d-block text-muted small mb-1">
                                    <i class="fi fi-rr-marker me-1 align-middle"></i>Rota
                                </div>
                                <h6 class="mb-0">
                                    {{ $fromLabel }} → {{ $toLabel }}
                                </h6>
                            </div>
                        </div>

                        {{-- Yolcu Sayısı --}}
                        <div class="col-lg-6">
                            <div class="border rounded p-3">
                                <div class="d-block text-muted small mb-1">
                                    <i class="fi fi-rr-user me-1 align-middle"></i>Yolcular
                                </div>
                                <h6 class="mb-0">
                                    {{ $transferOffer['adults'] }} Yetişkin
                                    @if($transferOffer['children']) , {{ $transferOffer['children'] }} Çocuk @endif
                                    @if($transferOffer['infants']) , {{ $transferOffer['infants'] }} Bebek @endif
                                </h6>
                            </div>
                        </div>

                        {{-- Gidiş Tarihi --}}
                        @if(!empty($transferOffer['departure_date']))
                        <div class="col-lg-6">
                            <div class="border rounded p-3">
                                <div class="d-block text-muted small mb-1">
                                    <i class="fi fi-rr-calendar me-1 align-middle"></i>Gidiş Tarihi
                                </div>
                                <h6 class="mb-0">
                                    {{ \Carbon\Carbon::parse($transferOffer['departure_date'])
                                    ->locale(app()->getLocale())
                                    ->translatedFormat('d F Y') }}
                                </h6>
                            </div>
                        </div>
                        @endif

                        {{-- Dönüş Tarihi --}}
                        @if($transferOffer['direction'] === 'roundtrip' && !empty($transferOffer['return_date']))
                        <div class="col-lg-6">
                            <div class="border rounded p-3">
                                <div class="d-block text-muted small mb-1">
                                    <i class="fi fi-rr-calendar me-1 align-middle"></i>Dönüş Tarihi
                                </div>
                                <h6 class="mb-0">
                                    {{ \Carbon\Carbon::parse($transferOffer['return_date'])
                                    ->locale(app()->getLocale())
                                    ->translatedFormat('d F Y') }}
                                </h6>
                            </div>
                        </div>
                        @endif

                        {{-- Süre --}}
                        @if(!empty($transferOffer['estimated_duration_min']))
                        <div class="col-lg-6">
                            <div class="border rounded p-3">
                                <div class="d-block text-muted small mb-1">
                                    <i class="fi fi-rr-clock me-1 align-middle"></i>Süre
                                </div>
                                <h6 class="mb-0">
                                    ~ {{ $transferOffer['estimated_duration_min'] }} dk
                                </h6>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Rezervasyon Formu (placeholder, booking aşamasında bağlanacak) --}}
                <form method="POST" action="#">
                    @csrf

                    <input type="hidden" name="route_id" value="{{ $transferOffer['route_id'] }}">
                    <input type="hidden" name="vehicle_id" value="{{ $transferOffer['vehicle_id'] }}">
                    <input type="hidden" name="direction" value="{{ $transferOffer['direction'] }}">
                    <input type="hidden" name="from_location_id" value="{{ $transferOffer['from_location_id'] }}">
                    <input type="hidden" name="to_location_id" value="{{ $transferOffer['to_location_id'] }}">
                    <input type="hidden" name="departure_date" value="{{ $transferOffer['departure_date'] }}">
                    <input type="hidden" name="return_date" value="{{ $transferOffer['return_date'] }}">
                    <input type="hidden" name="adults" value="{{ $transferOffer['adults'] }}">
                    <input type="hidden" name="children" value="{{ $transferOffer['children'] }}">
                    <input type="hidden" name="infants" value="{{ $transferOffer['infants'] }}">
                    <input type="hidden" name="price_total" value="{{ $transferOffer['price_total'] }}">
                    <input type="hidden" name="currency" value="{{ $transferOffer['currency'] }}">

                    <div class="bg-light p-3 mt-3 rounded">
                        <div class="row">
                            {{-- Gidiş Saati --}}
                            <div class="col-lg-6 mb-3">
                                <label for="pickup_time_outbound" class="form-label">Gidiş Saati</label>
                                <div class="input-group">
                                    <input type="time"
                                           id="pickup_time_outbound"
                                           name="pickup_time_outbound"
                                           class="form-control"
                                           required>
                                    <span class="input-group-text bg-white">
                                            <i class="fi fi-rr-clock"></i>
                                        </span>
                                </div>
                            </div>

                            {{-- Gidiş Uçuş No (opsiyonel) --}}
                            <div class="col-lg-6 mb-3">
                                <label for="flight_number_outbound" class="form-label">Gidiş Uçuş Numarası</label>
                                <input type="text"
                                       id="flight_number_outbound"
                                       name="flight_number_outbound"
                                       class="form-control"
                                       placeholder="Örn: TK1234">
                            </div>

                            @if($transferOffer['direction'] === 'roundtrip')
                            {{-- Dönüş Saati --}}
                            <div class="col-lg-6 mb-3">
                                <label for="pickup_time_return" class="form-label">Dönüş Saati</label>
                                <div class="input-group">
                                    <input type="time"
                                           id="pickup_time_return"
                                           name="pickup_time_return"
                                           class="form-control"
                                           required>
                                    <span class="input-group-text bg-white">
                                                <i class="fi fi-rr-clock"></i>
                                            </span>
                                </div>
                            </div>

                            {{-- Dönüş Uçuş No (opsiyonel) --}}
                            <div class="col-lg-6 mb-3">
                                <label for="flight_number_return" class="form-label">Dönüş Uçuş Numarası</label>
                                <input type="text"
                                       id="flight_number_return"
                                       name="flight_number_return"
                                       class="form-control"
                                       placeholder="Örn: TK1235">
                            </div>
                            @endif

                            <div class="col-12">
                                <p class="text-muted small">
                                    <i class="fi fi-rr-info align-middle me-1"></i>
                                    Uçuş numaranızı girerseniz, uçak iniş saatine göre karşılama yapılır.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3 align-items-center">
                        <div class="col-12 col-xl-6 d-flex align-items-baseline gap-2">
                            <h2 class="m-0">
                                {{ number_format($transferOffer['price_total'], 0, ',', '.') }}
                                <small class="fs-6">{{ $transferOffer['currency'] }}</small>
                            </h2>
                            <span class="text-muted small">Toplam fiyat</span>
                        </div>
                        <div class="col-12 col-xl-6 d-grid mt-3 mt-xl-0">
                            <button type="submit" class="btn btn-primary">
                                Sepete Ekle
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @if($hasSearch && empty($transferOffer))
    <div class="bg-white p-4 rounded shadow-sm mb-4 mt-3">
        <p class="mb-0 text-muted">
            <i class="fi fi-rr-info me-1 align-middle"></i>
            Seçtiğiniz kriterlere uygun transfer bulunamadı. Lütfen farklı bir lokasyon veya kişi sayısı ile tekrar deneyin.
        </p>
    </div>
    @endif
</section>

<section class="container">
    <div class="row">
        <div class="row align-items-center text-center mb-2">
            <div class="col-lg-8 offset-lg-2 text-light p-4">
                <h1 class="text-secondary display-5 fw-bold mt-3 mt-lg-2">
                    Yolculuğunuz bizimle başlar,<br>konfor hiç bitmez.
                </h1>
                <p class="text-secondary">
                    Havalimanından otelinize kadar tüm yolculuklarınızda Mercedes Vito ve
                    Sprinter araçlarımızla size özel, konforlu ve güvenli transfer hizmeti sunuyoruz.
                </p>
            </div>
        </div>
    </div>
    <div class="rounded"
         style="min-height: 500px; background-image: url('/images/transfer-bg.png'); background-repeat: no-repeat; background-position: bottom; background-size: cover">
        <div class="row">
            <div class="col-lg-6 offset-lg-3 mb-2 mt-5">
                <div class="d-flex justify-content-center gap-3 text-secondary fs-4">
                    <div class="rounded-circle bg-light p-3"><i class="fi fi-sr-wifi d-block"></i></div>
                    <div class="rounded-circle bg-light p-3"><i class="fi fi-sr-air-conditioner d-block"></i></div>
                    <div class="rounded-circle bg-light p-3"><i class="fi fi-sr-charging-station d-block"></i></div>
                    <div class="rounded-circle bg-light p-3"><i class="fi fi-sr-martini-glass-citrus d-block"></i></div>
                    <div class="rounded-circle bg-light p-3"><i class="fi fi-sr-baby-carriage d-block"></i></div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-5">
            <h2 class="fw-bold mt-2 text-secondary">Öncelik güvenlik ve konfor</h2>
            <p class="text-muted mb-5">
                Tüm yolculuklarımızda üst düzey konfor ve güvenliği standart kabul ediyoruz.
                Araçlarımızda bulunan gelişmiş donanımlar sayesinde, havalimanından otelinize kadar
                olan her anı keyifle geçirmeniz için her şeyi düşündük.
            </p>
        </div>
        <div class="col-lg-7">
            <div class="row row-cols-1 text-start row-cols-md-2 g-3">
                <div class="col">
                    <i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Derin bagaj hacmi
                </div>
                <div class="col">
                    <i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Klima, Wifi ve mini buzdolabı
                </div>
                <div class="col">
                    <i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Profesyonel sürücüler
                </div>
                <div class="col">
                    <i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Bebek koltuğu opsiyonu
                </div>
                <div class="col">
                    <i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Deri koltuklar ve geniş iç hacim
                </div>
                <div class="col">
                    <i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>USB şarj ve multimedya sistemi
                </div>
                <div class="col">
                    <i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Karartmalı camlar
                </div>
                <div class="col">
                    <i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Özel iç aydınlatma
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fromSelect = document.getElementById('from_location_id');
        const toSelect = document.getElementById('to_location_id');
        const oneWayRadio = document.getElementById('oneway');
        const roundTripRadio = document.getElementById('roundtrip');
        const returnDateInput = document.getElementById('return_date');
        const departureDateInput = document.getElementById('departure_date');
        const guestInputVisible = document.getElementById('guestInput');

        // Nereden -> Nereye aynı lokasyonu engelle
        if (fromSelect && toSelect) {
            const originalToOptions = Array.from(toSelect.options).map(option => ({
                value: option.value,
                text: option.text
            }));

            function rebuildToOptions() {
                const fromVal = fromSelect.value;
                const prevToVal = toSelect.value;

                toSelect.innerHTML = '';

                originalToOptions.forEach(opt => {
                    if (opt.value === '' || opt.value !== fromVal) {
                        const o = document.createElement('option');
                        o.value = opt.value;
                        o.textContent = opt.text;
                        if (opt.value === prevToVal && opt.value !== fromVal) {
                            o.selected = true;
                        }
                        toSelect.appendChild(o);
                    }
                });

                if (!toSelect.value) {
                    const first = toSelect.querySelector('option[value=""]') || toSelect.options[0];
                    if (first) first.selected = true;
                }
            }

            rebuildToOptions();
            fromSelect.addEventListener('change', function () {
                rebuildToOptions();
                fromSelect.classList.remove('is-invalid');
            });
            toSelect.addEventListener('change', function () {
                toSelect.classList.remove('is-invalid');
            });
        }

        // Roundtrip ise dönüş tarihi zorunlu
        function syncReturnRequired() {
            if (!returnDateInput || !oneWayRadio || !roundTripRadio) return;
            returnDateInput.required = !!roundTripRadio.checked;
            // yön değişince varsa hata temizle
            returnDateInput.classList.remove('is-invalid');
        }
        syncReturnRequired();
        if (oneWayRadio && roundTripRadio) {
            oneWayRadio.addEventListener('change', syncReturnRequired);
            roundTripRadio.addEventListener('change', syncReturnRequired);
        }

        // Alan değişince invalid state'i anında kaldır
        if (departureDateInput) {
            departureDateInput.addEventListener('input', () => {
                if (departureDateInput.value.trim()) {
                    departureDateInput.classList.remove('is-invalid');
                }
            });
        }
        if (returnDateInput) {
            returnDateInput.addEventListener('input', () => {
                if (returnDateInput.value.trim()) {
                    returnDateInput.classList.remove('is-invalid');
                }
            });
        }

        // GuestPicker değişimini dinle (guestpicker.js içinden tetikleniyor)
        if (guestInputVisible) {
            document.addEventListener('guestCountChanged', function (e) {
                const total = e.detail && typeof e.detail.total === 'number'
                    ? e.detail.total
                    : 0;
                if (total > 0) {
                    guestInputVisible.classList.remove('is-invalid');
                }
            });
        }

        // Form validation: sadece kırmızı border, success yok
        const forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                let valid = true;

                // Direction
                const dirInputs = form.querySelectorAll('input[name="direction"]');
                const dirChecked = Array.from(dirInputs).some(i => i.checked);
                if (!dirChecked) {
                    valid = false;
                }

                // From / To
                if (fromSelect && !fromSelect.value) {
                    fromSelect.classList.add('is-invalid');
                    valid = false;
                }
                if (toSelect && !toSelect.value) {
                    toSelect.classList.add('is-invalid');
                    valid = false;
                }

                // Gidiş tarihi (zorunlu)
                if (!departureDateInput || !departureDateInput.value.trim()) {
                    if (departureDateInput) departureDateInput.classList.add('is-invalid');
                    valid = false;
                }

                // Roundtrip ise dönüş tarihi (zorunlu)
                if (roundTripRadio && roundTripRadio.checked) {
                    if (!returnDateInput || !returnDateInput.value.trim()) {
                        if (returnDateInput) returnDateInput.classList.add('is-invalid');
                        valid = false;
                    }
                }

                // Guest validation (min 1 yetişkin, toplam > 0)
                const adultInput = form.querySelector('input[name="adults"]');
                const childInput = form.querySelector('input[name="children"]');
                const infantInput = form.querySelector('input[name="infants"]');

                const adults = parseInt(adultInput ? adultInput.value : '0', 10) || 0;
                const children = parseInt(childInput ? childInput.value : '0', 10) || 0;
                const infants = parseInt(infantInput ? infantInput.value : '0', 10) || 0;
                const total = adults + children + infants;
                const guestValid = adults >= 1 && total > 0;

                if (!guestValid && guestInputVisible) {
                    guestInputVisible.classList.add('is-invalid');
                    valid = false;
                }

                if (!valid) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }, false);
        });
    });
</script>

@endsection
