@extends('layouts.app')

@section('title', 'Transferler')

@section('content')

    @php
        $loc = app()->getLocale();
        $c = $c ?? []; // controller'dan geliyor
    @endphp

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
            <h1 class="display-5 fw-bold text-secondary">
                {{ $c['page_header']['title'][$loc] ?? '' }}
            </h1>
            <p class="lead text-muted px-lg-5">
                {!! nl2br(e($c['page_header']['description'][$loc] ?? '')) !!}
            </p>
        </div>
    </section>

    <section class="container pb-5">
        <div class="bg-white p-4 rounded shadow-lg">
            {{-- Arama Formu --}}
            <form id="transferSearchForm"
                  class="row g-3 needs-validation"
                  action="{{ localized_route('transfers') }}"
                  method="GET"
                  novalidate>
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
                            <label for="departure_date" class="form-label">Geliş Tarihi</label>
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
                        $giAdults   = max(0, (int) request('adults', 2));
                        $giChildren = max(0, (int) request('children', 0));
                        $giInfants  = max(0, (int) request('infants', 0));
                        $giTotal    = $giAdults + $giChildren + $giInfants;
                        $giText     = $giTotal > 0
                        ? ($giAdults . ' Yetişkin'
                        . ($giChildren ? ', ' . $giChildren . ' Çocuk' : '')
                        . ($giInfants  ? ', ' . $giInfants  . ' Bebek'  : '')
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

        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Teklif Kartı: sadece uygun teklif varsa --}}
        @if(!empty($transferOffer))
            @php
                $fromLabel = collect($locations)->firstWhere('id', $transferOffer['from_location_id'])['label'] ?? '';
                $toLabel   = collect($locations)->firstWhere('id', $transferOffer['to_location_id'])['label'] ?? '';

                $gallery = $transferOffer['vehicle_gallery'] ?? [];
                if (empty($gallery)) {
                $placeholder = \App\Support\Helpers\ImageHelper::normalize(null);
                $placeholder['alt'] = $transferOffer['vehicle_name'] ?? 'Araç görseli';
                $gallery = [$placeholder];
                }
            @endphp

            <div class="bg-white p-4 rounded shadow-sm mb-4 mt-3">
                <div class="row">
                    {{-- Sol: Galeri --}}
                    <div class="col-lg-5 pe-lg-5 mb-4">
                        <div class="gallery">
                            <h4 class="fw-bold">{{ $transferOffer['vehicle_name'] }}</h4>
                            <p class="small text-muted">
                                Konforlu ve geniş araçlarımızla havalimanından konaklama noktanıza güvenli transfer.
                            </p>

                            <div
                                class="main-gallery position-relative bg-black d-flex align-items-center justify-content-center rounded mb-3"
                                style="height: 260px;">
                                @foreach($gallery as $index => $image)
                                    <img src="{{ $image['large'] }}"
                                         srcset="{{ $image['large'] }} 1x, {{ $image['large2x'] }} 2x"
                                         class="gallery-image position-absolute top-0 start-0 w-100 h-100 {{ $index !== 0 ? 'd-none' : '' }}"
                                         style="object-fit: contain;"
                                         alt="{{ $image['alt'] }}">
                                @endforeach
                            </div>

                            <div class="d-flex gap-2 overflow-auto thumbnail-scroll">
                                @foreach($gallery as $index => $image)
                                    <div class="flex-shrink-0 rounded overflow-hidden bg-black"
                                         data-gallery-thumb
                                         style="width: 72px; height: 72px; cursor: pointer;">
                                        <img src="{{ $image['thumb'] }}"
                                             srcset="{{ $image['thumb'] }} 1x, {{ $image['thumb2x'] }} 2x"
                                             class="w-100 h-100"
                                             style="object-fit: cover;"
                                             alt="{{ $image['alt'] }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Sağ: Özet + Rezervasyon --}}
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
                                                <i class="fi fi-rr-calendar me-1 align-middle"></i>Geliş Tarihi
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

                        {{-- Rezervasyon Formu --}}
                        <form id="transferBookForm"
                              method="POST"
                              action="{{ localized_route('transfer.book') }}"
                              novalidate>
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
                            <input type="hidden" name="from_label" value="{{ $fromLabel }}">
                            <input type="hidden" name="to_label" value="{{ $toLabel }}">
                            <input type="hidden" name="vehicle_image" value="{{ $transferOffer['vehicle_image'] ?? '' }}">
                            <input type="hidden" name="vehicle_name" value="{{ $transferOffer['vehicle_name'] ?? '' }}">

                            <div class="bg-light p-3 mt-3 rounded">
                                <div class="row">
                                    {{-- Gidiş Saati --}}
                                    <div class="col-lg-6 mb-3">
                                        <label for="pickup_time_outbound" class="form-label">Geliş Saati</label>
                                        <div class="input-group">
                                            <input type="time"
                                                   id="pickup_time_outbound"
                                                   name="pickup_time_outbound"
                                                   class="form-control">
                                            <span class="input-group-text bg-white">
                                            <i class="fi fi-rr-clock"></i>
                                        </span>
                                        </div>
                                    </div>

                                    {{-- Alınış Uçuş No (opsiyonel) --}}
                                    <div class="col-lg-6 mb-3">
                                        <label for="flight_number_outbound" class="form-label">
                                            Alınış Uçuş Numarası
                                        </label>
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
                                                       class="form-control">
                                                <span class="input-group-text bg-white">
                                                <i class="fi fi-rr-clock"></i>
                                            </span>
                                            </div>
                                        </div>

                                        {{-- Dönüş Uçuş No (opsiyonel) --}}
                                        <div class="col-lg-6 mb-3">
                                            <label for="flight_number_return" class="form-label">
                                                Dönüş Uçuş Numarası
                                            </label>
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
                                        <div id="bookPairError" class="text-danger small d-none">
                                            Saat veya Uçuş numarası alanlarından en az biri dolu olmalıdır.
                                        </div>
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

        @if (session('ok') === 'validated')
            <div class="alert alert-success mt-3">
                Rezervasyon bilgileri başarıyla doğrulandı.
            </div>
        @endif
    </section>

    @php
        $transferImg = $page->transfer_content_image ?? [];
        $transferBgUrl = $transferImg['large'] ?? '';
    @endphp

    <section class="container">
        <div class="row">
            <div class="row align-items-center text-center mb-2">
                <div class="col-lg-8 offset-lg-2 text-light p-4">
                    <h1 class="text-secondary display-5 fw-bold mt-3 mt-lg-2">
                        {!! nl2br(e($c['page_content']['title'][$loc] ?? '')) !!}
                    </h1>
                    <p class="text-secondary">
                        {!! nl2br(e($c['page_content']['description'][$loc] ?? '')) !!}
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded"
             style="min-height: 500px; background-image: url('{{ $transferBgUrl }}'); background-repeat: no-repeat; background-position: bottom; background-size: cover">
            <div class="row">
                <div class="col-lg-6 offset-lg-3 mb-2 mt-5">
                    @php
                        $icons = (array) ($c['page_content']['icons'] ?? []);
                    @endphp

                    @if(!empty($icons))
                        <div class="d-flex justify-content-center gap-3 text-secondary fs-4">
                            @foreach($icons as $it)
                                @php
                                    $raw = trim((string) ($it['icon'] ?? ''));
                                    $cls = $raw;
                                    // icon picker bazen "fi fi-..." bazen "fi-..." döndürebilir
                                    if ($cls !== '' && !str_contains($cls, 'fi ')) {
                                        $cls = 'fi ' . $cls;
                                    }
                                @endphp

                                @if($raw !== '')
                                    <div class="rounded-circle bg-light p-3">
                                        <i class="{{ $cls }} d-block"></i>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="container my-5">
        <div class="row">
            <div class="col-lg-5">
                <h2 class="fw-bold mt-2 text-secondary">
                    {{ $c['page_content']['content_title'][$loc] ?? '' }}
                </h2>
                <p class="text-muted mb-5">
                    {!! nl2br(e($c['page_content']['content_text'][$loc] ?? '')) !!}
                </p>
            </div>

            <div class="col-lg-7">
                @php
                    $features = (array) ($c['page_content']['features'][$loc] ?? []);
                @endphp

                <div class="row row-cols-1 text-start row-cols-md-2 g-3">
                    @foreach($features as $item)
                        @php
                            $text = (string) ($item['text'] ?? '');
                        @endphp
                        @if($text !== '')
                            <div class="col">
                                <i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>{{ $text }}
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </section>

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
                            if (opt.value === prevToVal && opt.value !== fromVal) o.selected = true;
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
                returnDateInput.classList.remove('is-invalid');
            }
            syncReturnRequired();
            if (oneWayRadio && roundTripRadio) {
                oneWayRadio.addEventListener('change', syncReturnRequired);
                roundTripRadio.addEventListener('change', syncReturnRequired);
            }

            // Alan değişince invalid state'i anında kaldır
            departureDateInput?.addEventListener('input', () => {
                if (departureDateInput.value.trim()) departureDateInput.classList.remove('is-invalid');
            });
            returnDateInput?.addEventListener('input', () => {
                if (returnDateInput.value.trim()) returnDateInput.classList.remove('is-invalid');
            });

            // GuestPicker değişimi (guestpicker.js tetikler)
            if (guestInputVisible) {
                document.addEventListener('guestCountChanged', function (e) {
                    const total = e.detail && typeof e.detail.total === 'number' ? e.detail.total : 0;
                    if (total > 0) guestInputVisible.classList.remove('is-invalid');
                });
            }

            // --- SADECE ARAMA FORMU doğrulama ---
            const searchForm = document.getElementById('transferSearchForm');
            if (searchForm) {
                searchForm.addEventListener('submit', function (event) {
                    let valid = true;

                    // Direction
                    const dirInputs = searchForm.querySelectorAll('input[name="direction"]');
                    const dirChecked = Array.from(dirInputs).some(i => i.checked);
                    if (!dirChecked) valid = false;

                    // From / To
                    if (fromSelect && !fromSelect.value) { fromSelect.classList.add('is-invalid'); valid = false; }
                    if (toSelect && !toSelect.value) { toSelect.classList.add('is-invalid'); valid = false; }

                    // Tarihler
                    if (!departureDateInput || !departureDateInput.value.trim()) {
                        departureDateInput?.classList.add('is-invalid');
                        valid = false;
                    }
                    if (roundTripRadio && roundTripRadio.checked) {
                        if (!returnDateInput || !returnDateInput.value.trim()) {
                            returnDateInput?.classList.add('is-invalid');
                            valid = false;
                        }
                    }

                    // Kişi (min 1 yetişkin)
                    const adults   = parseInt(searchForm.querySelector('input[name="adults"]')?.value || '0', 10);
                    const children = parseInt(searchForm.querySelector('input[name="children"]')?.value || '0', 10);
                    const infants  = parseInt(searchForm.querySelector('input[name="infants"]')?.value || '0', 10);
                    const total    = adults + children + infants;
                    const guestValid = adults >= 1 && total > 0;
                    if (!guestValid && guestInputVisible) {
                        guestInputVisible.classList.add('is-invalid');
                        valid = false;
                    }

                    if (!valid) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                });
            }

            // --- BOOKING FORMU: Saat veya Uçuş No çiftlerinden en az biri ---
            const bookForm = document.getElementById('transferBookForm');
            if (bookForm) {
                const outTime   = document.getElementById('pickup_time_outbound');
                const outFlight = document.getElementById('flight_number_outbound');
                const retTime   = document.getElementById('pickup_time_return');   // tek yönse yok
                const retFlight = document.getElementById('flight_number_return'); // tek yönse yok
                const pairError = document.getElementById('bookPairError');

                function pairValid(timeEl, flightEl) {
                    const a = !!(timeEl && timeEl.value && timeEl.value.trim());
                    const b = !!(flightEl && flightEl.value && flightEl.value.trim());
                    return a || b;
                }
                function markPairInvalid(timeEl, flightEl) {
                    timeEl?.classList.add('is-invalid');
                    flightEl?.classList.add('is-invalid');
                }
                function clearPairInvalid(timeEl, flightEl) {
                    timeEl?.classList.remove('is-invalid');
                    flightEl?.classList.remove('is-invalid');
                }

                [outTime, outFlight, retTime, retFlight].forEach(el => {
                    el?.addEventListener('input', () => {
                        clearPairInvalid(outTime, outFlight);
                        clearPairInvalid(retTime, retFlight);
                        pairError?.classList.add('d-none');
                    });
                });

                bookForm.addEventListener('submit', function (e) {
                    let ok = true;

                    // Alınış çifti zorunlu
                    if (!pairValid(outTime, outFlight)) { markPairInvalid(outTime, outFlight); ok = false; }

                    // Dönüş çifti, dönüş varsa zorunlu
                    if (retTime || retFlight) {
                        if (!pairValid(retTime, retFlight)) { markPairInvalid(retTime, retFlight); ok = false; }
                    }

                    if (!ok) {
                        e.preventDefault();
                        e.stopPropagation();
                        pairError?.classList.remove('d-none');
                    }
                });
            }
        });
    </script>

@endsection
