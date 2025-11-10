@extends('layouts.app')

@section('title', $villa['name']['tr'])

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col">
            <!-- Başlık -->
            <h1 class="mb-1">{{ $villa['name']['tr'] }}</h1>

            <!-- Temel Özellik -->
            <div class="d-flex gap-3 mb-3 text-secondary">
                <div>
                    <i class="fi fi-rs-user align-middle"></i> <span class="small">8 Kişi</span>
                </div>
                <div>
                    <i class="fi fi-rs-bed-alt align-middle"></i> <span class="small">3 Yatak Odası</span>
                </div>
                <div>
                    <i class="fi fi-rs-shower align-middle"></i> <span class="small">2 Banyo</span>
                </div>
            </div>

            <!-- Konum -->
            <p class="text-muted">{{ $villa['location']['city'] }}, {{ $villa['location']['region'] }}</p>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-xl-8">
            <!-- Galeri -->
            <div class="gallery">
                <div class="main-gallery position-relative mb-3 bg-black d-flex align-items-center justify-content-center rounded-3"
                     style="height: 420px;">
                    @foreach ($villa['gallery'] as $index => $img)
                    <img src="{{ asset($img) }}"
                         class="gallery-image position-absolute top-0 start-0 w-100 h-100 {{ $index === 0 ? '' : 'd-none' }}"
                         style="object-fit: contain;"
                         data-index="{{ $index }}"
                         alt="Görsel {{ $index + 1 }}">
                    @endforeach
                </div>

                <!-- Thumbnail -->
                <div class="d-flex overflow-auto gap-2 pb-2 thumbnail-scroll data-gallery-thumbs">
                    @foreach ($villa['gallery'] as $index => $img)
                    <div class="flex-shrink-0 overflow-hidden bg-black rounded"
                         style="width: 92px; height: 92px; cursor: pointer;"
                         data-gallery-thumb>
                        <img src="{{ asset($img) }}"
                             class="w-100 h-100"
                             style="object-fit: cover; object-position: center;"
                             alt="Thumb {{ $index + 1 }}">
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Rezervasyon Formu -->
            @php
            $price = $villa['prices']['TRY'];
            $discountedPrice = round($price * 0.85);
            @endphp

            <div class="card shadow-sm my-4">
                <!-- Header Kapmanya -->
                <div class="card-header bg-danger text-white text-center small">
                    <i class="fi fi-rs-user align-middle"></i> yeni üyelere 15% indirim!</span>
                </div>
                <div class="card-body">
                    <form id="villa-booking-form">
                        <div class="row">
                            <!-- Sol Sütun -->
                            <div class="col-lg-8">
                                <!-- Giriş Tarihi -->
                                <label for="checkin" class="form-label">Giriş ve Çıkış Tarihi</label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        id="checkin"
                                        name="checkin"
                                        class="form-control date-input"
                                        placeholder="gg.aa.yyyy"
                                        autocomplete="off"
                                        data-price="{{ $discountedPrice }}"
                                        data-unavailable='@json($villa["unavailable_dates"] ?? [])'
                                    >
                                    <div class="input-group-text bg-white">
                                            <i class="fi fi-rr-calendar"></i>
                                        </div>
                                    <div id="min-nights-feedback" class="invalid-feedback d-block d-none">
                                        En az 3 gece seçmelisiniz.
                                    </div>
                                </div>
                                <input type="hidden" name="checkin" id="hidden-checkin">
                                <input type="hidden" name="checkout" id="hidden-checkout">

                            </div>

                            <!-- Sağ Sütun -->
                            <div class="col-lg-4 mt-4 mt-lg-0 d-flex justify-content-end align-items-end text-end" id="villa-price-box"
                                 data-price="{{ $price }}"
                                 data-discount="{{ $discountedPrice }}">

                                {{-- Giriş öncesi görünüm --}}
                                <div id="price-before-selection">
                                    <div class="text-muted text-decoration-line-through small" id="price-original">
                                        {{ number_format($price, 0, ',', '.') }}₺
                                    </div>
                                    <div class="fs-5 fw-semibold text-primary" id="price-discounted">
                                            Gecelik: {{ number_format($discountedPrice, 0, ',', '.') }}₺
                                    </div>
                                </div>

                                {{-- Giriş sonrası görünüm --}}
                                <div id="price-after-selection" class="d-none">
                                    <div class="small text-muted" id="price-multiplied">
                                        <!-- örn: 6 x 8.500₺ -->
                                    </div>
                                    <div class="small text-decoration-line-through" id="price-total-original">
                                        <!-- örn: Toplam: 51.000₺ -->
                                    </div>
                                    <div class="fs-5 fw-bold text-primary" id="price-total-discounted">
                                        <!-- örn: Toplam: 43.350₺ -->
                                    </div>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>

                <!-- Footer Buton -->
                <div class="card-footer bg-secondary-subtle text-white text-center">
                    <strong>Rezervasyon Yap</strong>
                </div>
            </div>


            <!-- Açıklama -->
            <div class="mb-4">
                <p>{{ $villa['description']['tr'] }}</p>
            </div>

            <!-- Villa Tipi Badge'leri -->
            <div class="mb-3">
                @foreach ($villa['types'] as $type)
                <span class="badge bg-secondary me-1">{{ $type }}</span>
                @endforeach
            </div>

            @if (!empty($villa['highlights']['tr']))
            <div class="mb-4 bg-light p-4 rounded shadow-sm" id="highlight-section">
                <h5 class="mb-3">Öne Çıkan Özellikler</h5>
                <div class="row">
                    @foreach ($villa['highlights']['tr'] as $item)
                    <div class="col-12 mb-2 d-flex align-items-baseline">
                        <i class="fi fi-ss-badge-check me-2 mt-2 text-success"></i>
                        <span>{{ $item }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if (!empty($villa['facilities']['tr']))
            <div class="mb-4 bg-light p-4 rounded shadow-sm" id="facility-section">
                <h5 class="mb-3">Villa Olanakları</h5>
                <div class="row">
                    @foreach ($villa['facilities']['tr'] as $item)
                    <div class="col-6 mb-2 d-flex align-items-baseline">
                        <i class="fi fi-br-check me-2 text-success"></i>
                        <span>{{ $item }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if (!empty($villa['accommodation_info']['tr']))
            <div class="mb-4 bg-light p-4 rounded shadow-sm" id="accommodation-info-section">
                <h5 class="mb-3">Konaklama Hakkında</h5>
                <ul class="list-unstyled mb-0">
                    @foreach ($villa['accommodation_info']['tr'] as $item)
                    <li class="mb-2 d-flex align-items-baseline">
                        <i class="fi fi-rr-info me-2 mt-2 text-info"></i>
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

        </div>

        <!-- Sağ Sütun -->
        <div class="col-xl-4">
            <!-- Kampanya Banner -->
            <div class="mb-4 position-relative text-white rounded shadow bg-primary rounded" style="min-height: 200px;">
                <div class="position-absolute bottom-0" style="right:-100px; z-index: 1; overflow: hidden; width: 280px;">
                    <img src="/images/banner-woman.png" alt="Kampanya" class="img-fluid">
                </div>
                <div class="position-relative p-4" style="z-index: 2;">
                    <h6 class="fw-light mb-0">Hoş geldin hediyesi!</h6>
                    <h2 class="fw-bold mb-2" style="color: hotpink">%15 indirim</h2>
                    <p class="mb-3 text-shadow-transparent w-75 small">
                        İlk rezervasyonunuzda geçerli <strong class="d-inline-block whitespace-nowrap">%15 indirim</strong> fırsatı!
                    </p>
                    <a href="#" class="btn btn-outline-light fw-semibold btn-sm">Hesap Oluştur</a>
                </div>
            </div>

            <!-- Transfer Banner -->
            <div class="mb-4 position-relative text-white rounded shadow bg-secondary rounded" style="min-height: 160px;">
                <div class="position-absolute bottom-0" style="right:-15px; z-index: 1; overflow: hidden; width: 220px;">
                    <img src="/images/vito.png" alt="Transfer" class="img-fluid">
                </div>
                <div class="position-relative p-4" style="z-index: 2;">
                    <h6 class="fw-light mb-0">7 Gece rezervasyonunuza</h6>
                    <h4 class="fw-bold mb-2">Ücretsiz Transfer</h4>
                    <a href="#" class="btn btn-outline-light fw-semibold mt-3 btn-sm">Havaalanı Transferi</a>
                </div>
            </div>

            <!-- Google Maps -->
            <div class="bg-light p-4 rounded shadow-sm">
                <!-- Harita -->
                <div class="ratio ratio-16x9 rounded shadow-sm overflow-hidden mb-4">
                    <iframe
                        src="https://www.google.com/maps?q=41.061296,28.987531&hl=tr&z=16&output=embed"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                @if (!empty($villa['nearby_places']))
                <h5 class="mt-4 mb-3">Yakındaki Yerler</h5>
                <ul class="list-unstyled mb-0 small">
                    @foreach ($villa['nearby_places'] as $place)
                    <li class="mb-2 d-flex align-items-start">
                        <i class="fi {{ $place['icon'] }} me-2 text-primary fs-5"></i>
                        {{ $place['label'] }} — <strong class="ms-1">{{ $place['value'] }}</strong>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
