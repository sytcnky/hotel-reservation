@extends('layouts.app')

@section('title', 'Anasayfa')

@section('content')

    @php
        $loc = app()->getLocale();
        $c = $page->content ?? [];
    @endphp

    {{-- HERO --}}
    <section class="position-relative overflow-hidden" style="min-height: 500px;">
        {{-- Arka plan görsel --}}
        <x-responsive-image
            :image="$page->home_hero_background_image"
            preset="gallery"
            sizes="100vw"
            class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover"
        />


        <!-- Karanlık / gradient Overlay (mevcut css) -->
        <div class="hero-overlay"></div>

        <!-- İçerik -->
        <div class="container position-relative z-1 d-flex flex-column justify-content-start align-items-center pt-5"
             style="min-height: 500px;">
            <div class="text-center">
                <h1 class="display-4 fw-bold mt-5 text-white">
                    <small class="fs-3 fw-light">{{ $c['hero']['eyebrow'][$loc] ?? '' }}</small><br/>
                    {{ $c['hero']['title'][$loc] ?? '' }}
                </h1>
                <p class="lead text-light mb-4">{{ $c['hero']['subtitle'][$loc] ?? '' }}</p>
            </div>


            <!-- Sekmeler -->
            <div class="w-100 rounded p-4" style="max-width: 960px;">
                <!-- Nav Tabs -->
                <ul class="nav nav-tabs hero-tabs justify-content-center border-0 gap-1 position-relative"
                    id="searchTabs" role="tablist">

                    {{-- HERO Transparent --}}
                    <x-responsive-image
                        :image="$page->home_hero_transparent_image"
                        preset="listing-card"
                        class="hero-tabs-girl position-absolute bottom-0 end-0"
                    />
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-otel" data-bs-toggle="tab" data-bs-target="#content-otel"
                                type="button" role="tab">Konaklama
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-transfer" data-bs-toggle="tab" data-bs-target="#content-transfer"
                                type="button" role="tab">Transfer
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-villa" data-bs-toggle="tab" data-bs-target="#content-villa"
                                type="button" role="tab">Villa
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-tur" data-bs-toggle="tab" data-bs-target="#content-tur"
                                type="button" role="tab">Günlük Tur
                        </button>
                    </li>
                </ul>

                <!-- Tab İçerikleri -->
                <div class="tab-content bg-white rounded-3 p-3" id="searchTabsContent">
                    <div class="tab-pane fade show active" id="content-otel" role="tabpanel">
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
                    <div class="tab-pane fade" id="content-transfer" role="tabpanel">...</div>
                    <div class="tab-pane fade" id="content-villa" role="tabpanel">...</div>
                    <div class="tab-pane fade" id="content-tur" role="tabpanel">...</div>
                </div>
            </div>
        </div>
    </section>




    {{-- POPÜLER OTELLER --}}
    <section class="container my-5">
        <div class="row align-items-end">
            <div class="col">
                <div class="text-secondary">
                    <span class="mb-0">{{ $c['popular_hotels']['section_eyebrow'][$loc] ?? '' }}</span>
                    <h1 class="fs-3 fw-bold">{{ $c['popular_hotels']['section_title'][$loc] ?? '' }}</h1>
                </div>
            </div>
            <div class="col fs-2 text-end">
                <i class="fi fi-ss-arrow-circle-left text-secondary"></i>
                <i class="fi fi-ss-arrow-circle-right"></i>
            </div>
        </div>

        <div class="row g-4 align-items-stretch">

            <!-- Sol: Bölge tanıtım alanı -->
            <div class="col-md-6">
                <div class="position-relative h-100 rounded overflow-hidden text-white d-flex align-items-end p-4">

                    {{-- Arka plan görsel --}}
                    <x-responsive-image
                        :image="$page->home_popular_hotels_hero_image"
                        preset="gallery"
                        class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover"
                    />

                    {{-- Overlay --}}
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>

                    {{-- İçerik --}}
                    <div class="position-relative z-2">
                        <h3 class="fw-bold display-1 mb-3">
                            {{ $c['popular_hotels']['hero_title'][$loc] ?? '' }}
                        </h3>

                        <p class="mb-4">
                            {{ $c['popular_hotels']['description'][$loc] ?? '' }}
                        </p>

                        <a href="{{ $c['popular_hotels']['button']['href'][$loc] ?? '' }}"
                           class="btn btn-outline-light">
                            {{ $c['popular_hotels']['button']['text'][$loc] ?? '' }}
                        </a>
                    </div>

                </div>
            </div>


            <!-- Sağ: Otel kartları -->
            <div class="col-md-6">
                <div class="d-flex flex-column gap-3 column-gap-2">
                    @foreach($popularHotels as $hotel)
                        @php
                            $hotelName = $pickLocale($hotel->name) ?? '';
                        @endphp

                        <div class="card h-100 shadow-sm">
                            <div class="card-body">
                                <div class="row align-items-end">
                                    <!-- Otel görseli -->
                                    <div class="col-lg-3 mb-3 mb-lg-0">
                                        <a href="#">
                                            <x-responsive-image
                                                :image="$hotel->cover_image"
                                                preset="listing-card"
                                                class="img-fluid rounded object-fit-cover w-100"
                                            />
                                        </a>
                                    </div>

                                    <!-- Otel bilgileri -->
                                    <div class="col-lg-6 mb-3 mb-lg-0">
                                        <h5 class="card-title mb-1">{{ $hotelName }}</h5>
                                        <div class="mb-1 d-flex align-items-center gap-1">
                                            <i class="fi fi-ss-star text-warning"></i>
                                            <i class="fi fi-ss-star text-warning"></i>
                                            <i class="fi fi-ss-star text-warning"></i>
                                            <i class="fi fi-ss-star text-warning"></i>
                                            <i class="fi fi-rs-star text-warning"></i>
                                            <span class="ms-2 text-secondary"></span>
                                        </div>
                                        <div class="text-muted"></div>
                                    </div>

                                    <!-- Fiyat ve buton -->
                                    <div class="col-lg-3 text-lg-end">
                                        <p class="mb-1 fw-semibold text-dark"></p>
                                        <div class="d-grid mt-2">
                                            <a href="#" class="btn btn-outline-primary">Oteli İncele</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </section>

    {{-- KAMPANYA CAROUSEL (ORİJİNAL HALİYLE KORUNDU) --}}
    <div class="container mt-5">
        <section id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
            <!-- Carousel Items -->
            <div class="carousel-inner rounded">
                <div class="carousel-item active">
                    <div class="hero-slide position-relative p-5"
                         style="background-image: url('/images/samples/slide-marmaris.jpg');">
                        <div class="d-flex flex-column flex-lg-row position-relative z-3 text-white gap-4">
                            <div class="flex-lg-grow-1">
                                <h3 class="fw-bold mb-3 te">İlk rezervasyonunuza özel</h3>
                                <div class="display-1">%15 indirim!</div>
                                <p class="lead mb-4">Onlarca tesis arasından seçiminizi yapın, avantajlı fiyatlarla tatilin
                                    tadını çıkarın.</p>
                            </div>
                            <div>
                                <div class="d-grid">
                                    <a href="/register" class="btn btn-outline-light btn-lg px-2 mb-4 px-lg-5">Hemen Üye
                                        Ol</a>
                                </div>
                            </div>
                        </div>
                        <div class="overlay z-1 bg-dark opacity-50"></div>
                    </div>
                </div>
                <div class="carousel-item">
                    <div class="hero-slide position-relative p-5"
                         style="background-image: url('/images/samples/slide-summer.jpg');">
                        <div class="d-flex flex-column flex-lg-row position-relative z-3 text-white gap-4">
                            <div class="flex-lg-grow-1">
                                <h3 class="fw-bold mb-3">Otel + Transfer + Tur</h3>
                                <div class="display-1">%10 paket indirimi!</div>
                                <p class="lead mb-4">Tatilinizi paket alın avantajlı fırsatı kaçırmayın.</p>
                            </div>
                            <div>
                                <div class="d-grid">
                                    <a href="/register" class="btn btn-outline-light btn-lg px-2 mb-4 px-lg-5">Tatil
                                        Paketleri</a>
                                </div>
                            </div>
                        </div>
                        <div class="overlay z-1 bg-dark opacity-50"></div>
                    </div>
                </div>
            </div>

            <!-- Carousel Indicators -->
            <div class="carousel-indicators position-absolute bottom-0 mb-4">
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true"
                        aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            </div>
        </section>
    </div>

    {{-- GEZİ REHBERİ --}}
    <section class="container my-5">
        <div class="row">
            <div class="col-12 col-xl-4 text-secondary">
                <h1 class="display-1 lh-1 mb-5">{{ $c['travel_guides']['hero_title'][$loc] ?? '' }}</h1>
                <h2 class="fs-3 fw-bold mb-5">{{ $c['travel_guides']['title'][$loc] ?? '' }}</h2>

                <p>{!! nl2br(e($c['travel_guides']['description'][$loc] ?? '')) !!}</p>

                <a href="#" class="btn btn-outline-secondary btn-sm">Tüm Rehberler</a>
            </div>

            <div class="col-12 col-xl-8 ps-xl-5">
                <div class="row g-4">
                    @foreach($travelGuides as $guide)
                        @php
                            $guideTitle = $pickLocale($guide->title) ?? '';
                            $guideDesc  = $pickLocale($guide->excerpt ?? []) ?? '';
                        @endphp

                        <div class="col-xl-6">
                            <div class="position-relative h-100 rounded overflow-hidden text-white d-flex align-items-end p-4">
                                <x-responsive-image
                                    :image="$guide->cover_image"
                                    preset="listing-card"
                                    class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover"
                                />

                                <div class="position-relative z-2">
                                    <h3 class="fw-bold mt-5">{{ $guideTitle }}</h3>
                                    <p class="mb-4">{{ $guideDesc }}</p>
                                    <a href="#" class="btn btn-outline-light">Gezi Rehberi</a>
                                </div>

                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-75"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

@endsection
