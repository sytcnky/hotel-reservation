@extends('layouts.app', ['pageKey' => 'villa-details'])
@section('title', $villa['name'])
@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col">
            <!-- Başlık -->
            <h1 class="mb-1">{{ $villa['name'] }}</h1>

            <!-- Temel Özellik -->
            <div class="d-flex gap-3 mb-3 text-secondary small">
                @if(!empty($villa['max_guests']))
                <div>
                    <i class="fi fi-rs-user align-middle"></i>
                    <span>{{ $villa['max_guests'] }} Kişi</span>
                </div>
                @endif
                @if(!empty($villa['bedroom_count']))
                <div>
                    <i class="fi fi-rs-bed-alt align-middle"></i>
                    <span>{{ $villa['bedroom_count'] }} Yatak Odası</span>
                </div>
                @endif
                @if(!empty($villa['bathroom_count']))
                <div>
                    <i class="fi fi-rs-shower align-middle"></i>
                    <span>{{ $villa['bathroom_count'] }} Banyo</span>
                </div>
                @endif
            </div>

            <!-- Konum -->
            <p class="text-muted mb-0">
                {{ $villa['location']['city'] ?? '' }}
                @if(!empty($villa['location']['region']))
                , {{ $villa['location']['region'] }}
                @endif
            </p>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-xl-8">
            <!-- Galeri -->
            <div class="gallery">
                @php
                    $galleryImages = $villa['gallery'] ?? [];

                    if (empty($galleryImages)) {
                        $galleryImages = [$villa['cover']];
                    }
                @endphp

                <div class="main-gallery position-relative mb-3 bg-black d-flex align-items-center justify-content-center rounded-3"
                     style="height: 420px;">
                    @foreach ($galleryImages as $index => $img)
                    <x-responsive-image
                        :image="$img"
                        preset="gallery"
                        class="gallery-image position-absolute top-0 start-0 w-100 h-100 {{ $index === 0 ? '' : 'd-none' }}"
                        style="object-fit: contain;"
                        :data-index="$index"
                    />
                    @endforeach
                </div>

                <!-- Thumbnails -->
                <div class="d-flex overflow-auto gap-2 pb-2 thumbnail-scroll" data-gallery-thumbs>
                    @foreach ($galleryImages as $index => $img)
                    <div class="flex-shrink-0 overflow-hidden bg-black rounded"
                         style="width: 92px; height: 92px; cursor: pointer;"
                         data-gallery-thumb>
                        <x-responsive-image
                            :image="$img"
                            preset="gallery-thumb"
                            class="w-100 h-100"
                            style="object-fit: cover; object-position: center;"
                        />
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="row my-2">
                <div class="col">
                    <small class="small text-info">
                    <i class="fi fi-rr-info align-middle me-1"></i>Villa'larda sadece ön ödeme yaparsınız, kalan tutar konaklama sırasında nakit yapılır.
                    </small>
                </div>
            </div>

            {{-- Rezervasyon Formu (tarih + kişi + fiyat kutusu) --}}
            @php
            $price          = $villa['base_price'] ?? null;
            $currency       = \App\Support\Currency\CurrencyContext::code();
            $prepaymentRate = (float) ($villa['prepayment_rate'] ?? 0);
            $hasPrice       = ! is_null($price);

            $initialAdults   = (int) request('adults', 2);
            $initialChildren = (int) request('children', 0);
            @endphp

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form id="villa-booking-form"method="POST"
                          action="{{ localized_route('villa.book') }}">
                        @csrf

                        <input type="hidden" name="villa_id" value="{{ $villa['id'] }}">
                        <input type="hidden" name="checkin"  id="hidden-checkin">
                        <input type="hidden" name="checkout" id="hidden-checkout">
                        <input type="hidden" name="nights"   id="villa-nights">

                        {{-- Fiyat alanları (server’a sayısal değerler gitsin) --}}
                        <input type="hidden" name="currency"         value="{{ $currency }}">
                        <input type="hidden" name="price_nightly"    id="villa-price-nightly">
                        <input type="hidden" name="price_prepayment" id="villa-price-prepayment">
                        <input type="hidden" name="price_total"      id="villa-price-total">

                        {{-- Snapshot için isim --}}
                        <input type="hidden" name="villa_name" value="{{ $villa['name'] }}">

                        {{-- İstersen lokasyon etiketi de gönderebilirsin (opsiyonel) --}}
                        @php
                        $locationLabel = trim(($villa['location']['city'] ?? '') . ' ' . ($villa['location']['region'] ?? ''));
                        @endphp
                        @if($locationLabel !== '')
                        <input type="hidden" name="location_label" value="{{ $locationLabel }}">
                        @endif

                        <div class="row">
                            <!-- Sol Sütun: Tarih -->
                            <div class="col-lg-4">
                                <label for="checkin" class="form-label">Giriş ve Çıkış Tarihi</label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        id="checkin"
                                        name="checkin_display"
                                        class="form-control date-input"
                                        placeholder="Tarih seçin"
                                        autocomplete="off"
                                        data-price="{{ $hasPrice ? $price : 0 }}"
                                        data-prepayment-rate="{{ $prepaymentRate }}"
                                        data-unavailable="[]"
                                    >
                                    <div class="input-group-text bg-white">
                                        <i class="fi fi-rr-calendar"></i>
                                    </div>
                                    <div id="min-nights-feedback" class="invalid-feedback d-block d-none">
                                        En az {{ $villa['min_nights'] }} gece seçmelisiniz.
                                    </div>
                                    <div id="max-nights-feedback" class="invalid-feedback d-block d-none">
                                        Bu villa maksimum {{ $villa['max_nights'] }} gece rezerve edilebilir.
                                    </div>
                                </div>
                            </div>

                            <!-- Orta Sütun: Kişi Sayısı (guestpicker) -->
                            <div class="col-lg-4 position-relative">
                                <label for="guestInput" class="form-label">Kişi Sayısı</label>

                                <div class="guest-picker-wrapper position-relative">
                                    <div class="input-group">
                                        <input type="text"
                                               id="guestInput"
                                               class="form-control guest-wrapper"
                                               placeholder="Kişi sayısı seçin"
                                               readonly>
                                        <span class="input-group-text bg-white">
                                <i class="fi fi-rr-user"></i>
                            </span>
                                    </div>

                                    <!-- Dropdown -->
                                    <div class="guest-dropdown border rounded shadow-sm bg-white p-3 position-absolute w-100"
                                         style="z-index: 10; top: 100%; display: none;">

                                        <!-- Yetişkin -->
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span>Yetişkin</span>
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <button type="button"
                                                        class="btn btn-outline-secondary minus"
                                                        data-type="adult">−</button>
                                                <input type="text"
                                                       class="form-control text-center"
                                                       data-type="adult"
                                                       value="{{ $initialAdults }}"
                                                       readonly>
                                                <button type="button"
                                                        class="btn btn-outline-secondary plus"
                                                        data-type="adult">+</button>
                                            </div>
                                        </div>

                                        <!-- Çocuk -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Çocuk</span>
                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                <button type="button"
                                                        class="btn btn-outline-secondary minus"
                                                        data-type="child">−</button>
                                                <input type="text"
                                                       class="form-control text-center"
                                                       data-type="child"
                                                       value="{{ $initialChildren }}"
                                                       readonly>
                                                <button type="button"
                                                        class="btn btn-outline-secondary plus"
                                                        data-type="child">+</button>
                                            </div>
                                        </div>

                                        <input type="hidden" name="adults" id="adultsInput" value="{{ $initialAdults }}">
                                        <input type="hidden" name="children" id="childrenInput" value="{{ $initialChildren }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Sağ Sütun: Fiyat Kutusu -->
                            <div
                                class="col-lg-4 mt-4 mt-lg-0 d-flex justify-content-end align-items-end text-end"
                                id="villa-price-box"
                                data-price="{{ $hasPrice ? $price : 0 }}"
                                data-currency="{{ $currency }}"
                                data-prepayment="{{ $villa['prepayment_rate'] ?? 0 }}"
                                data-min-nights="{{ $villa['min_nights'] ?? '' }}"
                                data-max-nights="{{ $villa['max_nights'] ?? '' }}"
                            >
                                {{-- Tarih seçilmemiş görünüm --}}
                                <div id="price-before-selection">
                                    @if($hasPrice)
                                        <div class="fs-5 fw-semibold text-primary">
                                            {{ \App\Support\Currency\CurrencyPresenter::format($price, $currency) }}
                                            <small>/ Gece</small>
                                        </div>
                                    @else
                                        <div class="text-muted small">
                                            Fiyat bilgisi bulunamadı
                                        </div>
                                    @endif
                                </div>

                                {{-- Tarih seçilmiş görünüm (JS sadece sayıları doldurur) --}}
                                <div id="price-after-selection" class="d-none">
                                    <div class="small text-muted">
                                        Gecelik:
                                        <span id="price-nightly"></span>
                                        ×
                                        <span id="price-nights"></span>
                                    </div>

                                    <div class="small fw-semibold text-danger">
                                        Ön ödeme:
                                        <span id="price-prepayment"></span>
                                    </div>

                                    <div class="fs-5 fw-bold text-primary mt-1">
                                        Toplam:
                                        <span id="price-total"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                                class="btn btn-primary w-100 mt-3">
                            Sepete Ekle
                        </button>
                    </form>
                </div>
            </div>

            <!-- Açıklama -->
            @if (!empty($villa['description']))
            <div class="mb-4">
                <p>{{ $villa['description'] }}</p>
            </div>
            @endif

            <!-- Villa Tipi / Kategori Badge -->
            @if (!empty($villa['category_name']))
            <div class="mb-3">
                <span class="badge bg-secondary me-1">{{ $villa['category_name'] }}</span>
            </div>
            @endif

            <!-- Öne Çıkan Özellikler -->
            @if (!empty($villa['highlights']))
            <div class="mb-4 bg-light p-4 rounded shadow-sm" id="highlight-section">
                <h5 class="mb-3">Öne Çıkan Özellikler</h5>
                <div class="row">
                    @foreach ($villa['highlights'] as $item)
                    <div class="col-12 mb-2 d-flex align-items-baseline">
                        <i class="fi fi-ss-badge-check me-2 mt-2 text-success"></i>
                        <span>{{ $item }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Konaklama Hakkında -->
            @if (!empty($villa['accommodation_info']))
            <div class="mb-4 bg-light p-4 rounded shadow-sm" id="accommodation-info-section">
                <h5 class="mb-3">Konaklama Hakkında</h5>
                <ul class="list-unstyled mb-0">
                    @foreach ($villa['accommodation_info'] as $item)
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
            @include('partials.campaigns.banner', ['campaigns' => $campaigns ?? []])

            <!-- Google Maps + Yakındaki Yerler -->
            <div class="bg-light p-4 rounded shadow-sm">
                <div class="ratio ratio-16x9 rounded shadow-sm overflow-hidden mb-4">
                    @if (!empty($villa['latitude']) && !empty($villa['longitude']))
                    <iframe
                        src="https://www.google.com/maps?q={{ $villa['latitude'] }},{{ $villa['longitude'] }}&hl={{ app()->getLocale() }}&z=16&output=embed"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                    @else
                    <div class="d-flex align-items-center justify-content-center h-100 text-muted small">
                        Konum bilgisi yakında eklenecek.
                    </div>
                    @endif
                </div>

                @if (!empty($villa['nearby_places']))
                <h5 class="mt-4 mb-3">Yakındaki Yerler</h5>
                <ul class="list-unstyled mb-0 small">
                    @foreach ($villa['nearby_places'] as $place)
                    <li class="mb-2 d-flex align-items-start">
                        @if (!empty($place['icon']))
                        <i class="fi {{ $place['icon'] }} me-2 text-primary fs-5"></i>
                        @endif
                        {{ $place['label'] ?? '' }}
                        @if (!empty($place['value']))
                        — <strong class="ms-1">{{ $place['value'] }}</strong>
                        @endif
                    </li>
                    @endforeach
                </ul>
                @endif

                @if (!empty($villa['promo_video_id']))
                <hr class="my-4">
                <div class="ratio ratio-16x9 rounded overflow-hidden shadow-sm">
                    <iframe
                        src="https://www.youtube.com/embed/{{ $villa['promo_video_id'] }}?modestbranding=1&rel=0"
                        title="Tanıtım videosu"
                        allowfullscreen
                        loading="lazy"
                        style="border:0;">
                    </iframe>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
