@extends('layouts.app', ['pageKey' => 'transfer'])
@section('title', 'Transferler')
@section('content')

    @php
        $loc = app()->getLocale();
        $c = $c ?? []; // controller'dan geliyor
    @endphp

    <section>
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
                  autocomplete="off"
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
                    <div class="input-group">
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
                    <div class="input-group">
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
                                       placeholder="Tarih seçin"
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
                                       placeholder="Tarih seçin"
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

                $cover   = $transferOffer['vehicle_cover'] ?? null; // yeni cover (normalize image objesi)
                $gallery = $transferOffer['vehicle_gallery'] ?? []; // galeri aynen kalır, fallback yok
            @endphp

            <div class="bg-white p-4 rounded shadow-sm mb-4 mt-3">
                <div class="row">
                    {{-- Sol: Galeri --}}
                    <div class="col-lg-5 pe-lg-5 mb-4">
                        <div class="gallery">
                            @php
                                $gallery = $transferOffer['vehicle_gallery'] ?? [];
                                if (empty($gallery)) {
                                    $gallery = [$transferOffer['vehicle_cover']];
                                }
                            @endphp
                            <h4 class="fw-bold">{{ $transferOffer['vehicle_name'] }}</h4>
                            <p class="small text-muted">
                                Konforlu ve geniş araçlarımızla havalimanından konaklama noktanıza güvenli transfer.
                            </p>

                            <div
                                class="main-gallery position-relative bg-black d-flex align-items-center justify-content-center rounded mb-3"
                                style="height: 260px;">
                                @foreach($gallery as $index => $image)
                                    <x-responsive-image
                                        :image="$image"
                                        preset="gallery"
                                        class="gallery-image position-absolute top-0 start-0 w-100 h-100 {{ $index !== 0 ? 'd-none' : '' }}"
                                        style="object-fit: contain;"
                                    />
                                @endforeach
                            </div>

                            <div class="d-flex gap-2 overflow-auto thumbnail-scroll">
                                @foreach($gallery as $index => $image)
                                    <div class="flex-shrink-0 rounded overflow-hidden bg-black"
                                         data-gallery-thumb
                                         style="width: 72px; height: 72px; cursor: pointer;">
                                        <x-responsive-image
                                            :image="$image"
                                            preset="gallery-thumb"
                                            class="w-100 h-100"
                                            style="object-fit: cover;"
                                        />
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
                                                {{ \App\Support\Date\DatePresenter::human($transferOffer['departure_date']) }}
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
                                                {{ \App\Support\Date\DatePresenter::human($transferOffer['return_date']) }}
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
                            <input type="hidden" name="from_label" value="{{ $fromLabel }}">
                            <input type="hidden" name="to_label" value="{{ $toLabel }}">
                            <input type="hidden" name="vehicle_name" value="{{ $transferOffer['vehicle_name'] ?? '' }}">

                            <div class="bg-light p-3 mt-3 rounded">
                                <div class="row">
                                    {{-- Alınış --}}
                                    <div class="col-12">
                                        <label class="form-label fw-semibold">Alınış</label>
                                    </div>

                                    {{-- Radio (sol) --}}
                                    <div class="col-lg-6 mb-3">
                                        <div class="d-flex gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                       name="outbound_input_type"
                                                       id="outbound_time_radio"
                                                       value="time"
                                                       checked>
                                                <label class="form-check-label" for="outbound_time_radio">Saati</label>
                                            </div>

                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                       name="outbound_input_type"
                                                       id="outbound_flight_radio"
                                                       value="flight">
                                                <label class="form-check-label" for="outbound_flight_radio">Uçuş Numarası</label>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Input (sağ) --}}
                                    <div class="col-lg-6 mb-3">
                                        <div id="outbound_time_wrapper">
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

                                        <div id="outbound_flight_wrapper" class="d-none">
                                            <input type="text"
                                                   id="flight_number_outbound"
                                                   name="flight_number_outbound"
                                                   class="form-control"
                                                   placeholder="Örn: TK1234"
                                                   disabled>
                                        </div>
                                    </div>

                                    @if($transferOffer['direction'] === 'roundtrip')
                                        {{-- Dönüş --}}
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Dönüş</label>
                                        </div>

                                        {{-- Radio (sol) --}}
                                        <div class="col-lg-6 mb-3">
                                            <div class="d-flex gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                           name="return_input_type"
                                                           id="return_time_radio"
                                                           value="time"
                                                           checked>
                                                    <label class="form-check-label" for="return_time_radio">Saati</label>
                                                </div>

                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio"
                                                           name="return_input_type"
                                                           id="return_flight_radio"
                                                           value="flight">
                                                    <label class="form-check-label" for="return_flight_radio">Uçuş Numarası</label>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Input (sağ) --}}
                                        <div class="col-lg-6 mb-3">
                                            <div id="return_time_wrapper">
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

                                            <div id="return_flight_wrapper" class="d-none">
                                                <input type="text"
                                                       id="flight_number_return"
                                                       name="flight_number_return"
                                                       class="form-control"
                                                       placeholder="Örn: TK1235"
                                                       disabled>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-12">
                                        <p class="text-muted small">
                                            <i class="fi fi-rr-info align-middle me-1"></i>
                                            Seçtiğiniz seçeneğe göre saat veya uçuş numarası girmeniz gerekir.
                                        </p>

                                        <div id="bookPairError" class="text-danger small d-none">
                                            Seçili alanda bilgi girmelisiniz. (Saat veya Uçuş Numarası)
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3 align-items-center">
                                <div class="col-12 col-xl-6 d-flex align-items-end gap-2">
                                    <h2 class="m-0">
                                        <span class="text-muted fs-6">Toplam fiyat</span><br>
                                        {{ \App\Support\Currency\CurrencyPresenter::format($transferOffer['price_total'], $transferOffer['currency']) }}
                                    </h2>
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

        <div class="rounded position-relative overflow-hidden" style="min-height: 500px;">
            {{-- Arka plan görsel --}}
            <x-responsive-image
                :image="$page->transfer_content_image"
                preset="gallery"
                sizes="100vw"
                class="position-absolute bottom-0 start-0 w-100 h-100 object-fit-cover z-0"
            />
            <div class="row position-relative z-1">
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
@endsection
