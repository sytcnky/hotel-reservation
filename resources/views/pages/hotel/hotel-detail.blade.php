@extends('layouts.app')

@section('title', $hotel['name'])

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col">
            <!-- Otel Başlık -->
            <h1 class="mb-1">{{ $hotel['name'] }}</h1>

            <!-- Yıldız ve Lokasyon -->
            <p class="text-muted mb-0 d-flex gap-2 d-flex align-items-center">
            <div class="mb-3">
                @for ($i = 0; $i < $hotel['stars']; $i++)
                <i class="fi fi-ss-star text-warning"></i>
                @endfor
                @for ($i = $hotel['stars']; $i < 5; $i++)
                <i class="fi fi-rs-star text-warning"></i>
                @endfor
                <span class="text-secondary">{{ $hotel['location']['region'] }}, {{ $hotel['location']['city'] }}</span>
            </div>
        </div>
    </div>
    <div class="row g-5">
        <div class="col-xl-8">
            <!-- Galeri -->
            <div class="gallery">
                <div
                    class="main-gallery position-relative mb-3 bg-black d-flex align-items-center justify-content-center rounded-3"
                    style="height: 420px;">
                    @foreach ($hotel['images'] as $index => $img)
                    <img src="{{ asset($img) }}"
                         class="gallery-image position-absolute top-0 start-0 w-100 h-100 {{ $index === 0 ? '' : 'd-none' }}"
                         style="object-fit: contain;"
                         data-index="{{ $index }}"
                         alt="Görsel {{ $index + 1 }}">
                    @endforeach

                    <!-- Sol Alt: Video Tanıtım Butonu -->
                    <button type="button"
                            class="btn btn-light d-flex align-items-center gap-2 position-absolute start-0 bottom-0 m-3 px-3 py-2 shadow-sm"
                            style="border-radius: 999px; z-index: 5;">
                        <i class="fi fi-rr-play text-primary"></i>
                        <span class="fw-medium text-primary small">Tanıtım Videosu</span>
                    </button>

                </div>

                <!-- Thumbnail Satırı -->
                <div class="d-flex overflow-auto gap-2 pb-2 thumbnail-scroll data-gallery-thumbs">
                    @foreach ($hotel['images'] as $index => $img)
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

            <!-- Form -->
            <div class="border rounded p-3 my-4">
                <form id="booking-form" class="row g-3">
                    <!-- Giriş-Çıkış Tarihi -->
                    <div class="col-md-6">
                        <label for="checkin" class="form-label">Giriş - Çıkış Tarihi</label>
                        <div class="input-group">
                            <input type="text" id="checkin" name="checkin" class="form-control date-input"
                                   placeholder="gg.aa.yyyy" autocomplete="off">
                            <span class="input-group-text bg-white"><i class="fi fi-rr-calendar"></i></span>
                        </div>
                    </div>

                    <!-- Kişi Seçimi -->
                    <div class="col-md-4 position-relative">
                        <label for="guestInput" class="form-label">Kişi Sayısı</label>

                        <div class="guest-picker-wrapper position-relative">
                        <div class="input-group">
                            <input type="text" id="guestInput" class="form-control guest-wrapper" placeholder="Kişi sayısı seçin"
                                   readonly>
                            <span class="input-group-text bg-white"><i class="fi fi-rr-user"></i></span>
                        </div>

                        <!-- Dropdown -->
                        <div class="guest-dropdown border rounded shadow-sm bg-white p-3 position-absolute w-100"
                             style="z-index: 10; top: 100%; display: none;">
                            <!-- Yetişkin -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Yetişkin</span>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <button type="button" class="btn btn-outline-secondary minus" data-type="adult">−
                                    </button>
                                    <input type="text" class="form-control text-center" data-type="adult" value="2"
                                           readonly>
                                    <button type="button" class="btn btn-outline-secondary plus" data-type="adult">+
                                    </button>
                                </div>
                            </div>

                            <!-- Çocuk -->
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Çocuk</span>
                                <div class="input-group input-group-sm" style="width: 120px;">
                                    <button type="button" class="btn btn-outline-secondary minus" data-type="child">−
                                    </button>
                                    <input type="text" class="form-control text-center" data-type="child" value="0"
                                           readonly>
                                    <button type="button" class="btn btn-outline-secondary plus" data-type="child">+
                                    </button>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <div class="col-md-2 d-grid align-self-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fi fi-rr-search me-2"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Oda Listelemesi -->
            @foreach ($hotel['rooms'] as $room)
            @include('pages.hotel.room-card', ['room' => $room])
            @endforeach
        </div>
        <div class="col-xl-4">
            <!-- Kampanya Banner -->
            <div class="mb-4 position-relative text-white rounded shadow bg-primary rounded" style="min-height: 200px;">

                <div class="position-absolute bottom-0"
                     style="right:-100px; z-index: 1; overflow: hidden; width: 280px;">
                    <!-- Görsel -->
                    <img src="/images/banner-woman.png" alt="Kampanya Kadın" class="img-fluid">
                </div>

                <!-- İçerik -->
                <div class="position-relative p-4" style="z-index: 2;">
                    <h6 class="fw-light mb-0">Hoş geldin hediyesi!</h6>
                    <h2 class="fw-bold mb-2" style="color: hotpink">%15 indirim</h2>
                    <p class="mb-3 text-shadow-transparent w-75 small">İlk rezevasyonunuzda geçerli <strong
                            class="d-inline-block whitespace-nowrap">%15
                            indirim</strong> fırsatı!</p>
                    <a href="#" class="btn btn-outline-light fw-semibold btn-sm">Hesap Oluştur</a>
                </div>
            </div>

            <!-- Kampanya Banner -->
            <div class="mb-4 position-relative text-white rounded shadow bg-secondary rounded" style="min-height: 160px;">

                <div class="position-absolute bottom-0"
                     style="right:-15px; z-index: 1; overflow: hidden; width: 220px;">
                    <!-- Görsel -->
                    <img src="/images/vito.png" alt="Kampanya Kadın" class="img-fluid">
                </div>

                <!-- İçerik -->
                <div class="position-relative p-4" style="z-index: 2;">
                    <h6 class="fw-light mb-0">7 Gece otel rezervasyonunuza</h6>
                    <h4 class="fw-bold mb-2">Ücretsiz Transfer</h4>
                    <a href="#" class="btn btn-outline-light fw-semibold mt-3 btn-sm">Havaalanı Transferi</a>
                </div>
            </div>

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

                <!-- Yakındaki Lokasyonlar -->
                <h5 class="mb-3">Yakındaki Yerler</h5>
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2 d-flex align-items-start">
                        <i class="fi fi-rr-building me-2 text-primary fs-5"></i>
                        Şehir Merkezi — <strong class="ms-1">18 dk. yürüyüş</strong>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                        <i class="fi fi-rr-shop me-2 text-primary fs-5"></i>
                        Market — <strong class="ms-1">2 dk. sürüş</strong>
                    </li>
                    <li class="mb-2 d-flex align-items-start">
                        <i class="fi fi-rr-marker me-2 text-primary fs-5"></i>
                        Sedir Adası — <strong class="ms-1">4 dk. sürüş</strong>
                    </li>
                    <li class="d-flex align-items-start">
                        <i class="fi fi-rr-plane me-2 text-primary fs-5"></i>
                        Dalaman Havalimanı (DLM) — <strong class="ms-1">34 dk. sürüş</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xl-8">
            <!-- Otel Özellikleri -->
            @if(!empty($hotel['features']))
            @foreach($hotel['features'] as $featureGroup)
            <div class="mt-4 bg-light p-4 rounded shadow-sm">
                <h5 class="mb-4">{{ $featureGroup['category'] }}</h5>
                <div class="row">
                    @foreach($featureGroup['items'] as $item)
                    <div class="col-12 col-md-6 mb-2 d-flex align-items-center">
                        <i class="fi fi-br-check me-2 text-success"></i>
                        <span>{{ $item }}<span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
            @endif

            <!-- Notlar -->
            @if(!empty($hotel['notes']))
            <div class="mt-4 bg-light p-4 rounded shadow-sm">
                <h5 class="mb-4">Diğer Notlar</h5>
                <div class="row">
                    @foreach($hotel['notes'] as $note)
                    <div class="col-12 col-md-6 mb-2 d-flex align-items-baseline">
                        <i class="fi fi-br-square-info me-2 text-primary"></i>
                        <span>{{ $note }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Benzer Oteller --}}
<section class="container py-5">
    <div class="row">
        <div class="col">
            <h3 class="fw-bold mb-4">Benzer Oteller</h3>
        </div>
        <div class="col fs-2 text-end">
            <i class="fi fi-ss-arrow-circle-left text-secondary"></i>
            <i class="fi fi-ss-arrow-circle-right"></i>
        </div>
    </div>
    <div class="row g-4">
        @for ($i = 1; $i <= 4; $i++)
        <div class="col-6 col-lg-3">
            <div class="card h-100 shadow-sm border-0">
                <div class="ratio ratio-4x3">
                    <img src="/images/samples/hotel-{{ $i }}.jpg" class="card-img-top object-fit-cover" alt="Otel {{ $i }}">
                </div>
                <div class="card-body">
                    <h5 class="card-title fw-semibold">Otel Adı {{ $i }}</h5>
                    <i class="fi fi-ss-star text-warning"></i>
                    <i class="fi fi-ss-star text-warning"></i>
                    <i class="fi fi-ss-star text-warning"></i>
                    <i class="fi fi-ss-star text-warning"></i>
                    <i class="fi fi-rs-star text-warning"></i>
                    <p class="mb-1 text-muted">Marmaris, Muğla</p>
                    <div class="text-primary fw-bold mt-2">
                        3.200₺ / gece
                    </div>
                </div>
                <div class="card-footer bg-white border-0 pt-0">
                    <a href="#" class="btn btn-outline-primary w-100">Detayları Gör</a>
                </div>
            </div>
        </div>
        @endfor
    </div>
</section>

@endsection
