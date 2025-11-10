@extends('layouts.app')

@section('title', 'Transferler')

@section('content')

@php
$transfer = [
'vehicle_id' => 'vehicle-1',
'vehicle_name' => 'Mercedes Vito',
'images' => [
'/images/samples/arac-1.png',
'/images/samples/arac-2.png',
'/images/samples/arac-3.png',
'/images/samples/arac-4.png'
],
'capacity' => [
'passengers' => 3,
'luggage' => 3
],
'estimated_duration_min' => 90,
'features' => ['Klima', 'Wifi', 'Derin Bagaj'],

// Test amaçlı örnek route bilgileri (request fallback gibi)
'route_id' => 'route-istanbul-marmaris',
'from' => 'Dalaman',
'to' => 'Marmaris',
'direction' => 'roundtrip',
'departure_date' => '2025-07-24',
'return_date' => '2025-07-30',
'adults' => 2,
'children' => 1,
'infants' => 1
];
@endphp
<section>
    <div class="container mt-3" style="font-size: 14px">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#"><i class="fi fi-ss-house-chimney" style="vertical-align: middle"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Sayfa</li>
            </ol>
        </nav>
    </div>
    <div class="text-center my-5 px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-secondary">Yolculuğunuzun en kolay kısmı burası</h1>
        <p class="lead text-muted px-lg-5">Ulaşımınızı şansa bırakmayın. Tatil başlangıcınızdan dönüşünüze kadar
            konforlu ve güvenilir transfer hizmetleriyle yanınızdayız.</p>
    </div>
</section>
<section class="container pb-5">

    <div class="bg-white p-4 rounded shadow-lg">
        <form class="row g-3">
            <!-- Yön Seçimi -->
            <div class="col-12 text-center mb-2" style="margin-top: -25px">
                <div class="btn-group bg-white shadow-sm" role="group">
                    <input type="radio" class="btn-check" name="direction" id="oneway" value="oneway" checked>
                    <label class="btn btn-outline-primary" for="oneway">Tek Yön</label>

                    <input type="radio" class="btn-check" name="direction" id="roundtrip" value="roundtrip">
                    <label class="btn btn-outline-primary" for="roundtrip">Gidiş - Dönüş</label>
                </div>
            </div>

            <!-- Nereden -->
            <div class="col-lg-2">
                <label for="from" class="form-label">Nereden</label>
                <div class="input-group">
                    <select class="form-select" id="from" name="from">
                        <option>Dalaman</option>
                        <option>Bodrum</option>
                        <option>Antalya</option>
                    </select>
                    <span class="input-group-text bg-white"><i class="fi fi-rr-marker"></i></span>
                </div>
            </div>

            <!-- Nereye -->
            <div class="col-lg-2">
                <label for="to" class="form-label">Nereye</label>
                <div class="input-group">
                    <select class="form-select" id="to" name="to">
                        <option>İçmeler</option>
                        <option>Marmaris</option>
                        <option>Turunç</option>
                    </select>
                    <span class="input-group-text bg-white"><i class="fi fi-rr-marker"></i></span>
                </div>
            </div>

            <!-- Gidiş & Dönüş Tarihi -->
            <div class="col-lg-4">
                <div class="row g-3">
                    <!-- Gidiş -->
                    <div class="col">
                        <label for="departure_date" class="form-label">Gidiş Tarihi</label>
                        <div class="input-group">
                            <input type="text" class="form-control date-input" placeholder="gg.aa.yyyy"
                                   id="departure_date" name="departure_date">
                            <span class="input-group-text bg-white"><i class="fi fi-rr-calendar"></i></span>
                        </div>
                    </div>

                    <!-- Dönüş -->
                    <div class="col" id="returnDateWrapper">
                        <label for="return_date" class="form-label">Dönüş Tarihi</label>
                        <div class="input-group">
                            <input type="text" class="form-control date-input" placeholder="gg.aa.yyyy"
                                   id="return_date" name="return_date">
                            <span class="input-group-text bg-white"><i class="fi fi-rr-calendar"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kişi Sayısı -->
            <div class="col-lg-3">
                <label for="guestInput" class="form-label">Kişi Sayısı</label>

                <div class="guest-picker-wrapper position-relative">
                    <div class="input-group">
                        <input type="text" id="guestInput" class="form-control guest-wrapper" placeholder="Seçim yapın" readonly>
                        <span class="input-group-text bg-white"><i class="fi fi-rr-user"></i></span>
                    </div>

                    <!-- Dropdown -->
                    <div class="guest-dropdown border rounded shadow-sm bg-white p-3 position-absolute w-100"
                         style="z-index: 10; top: 100%; display: none;">
                        <!-- Yetişkin -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Yetişkin</span>
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <button type="button" class="btn btn-outline-secondary minus" data-type="adult">−</button>
                                <input type="text" class="form-control text-center" data-type="adult" value="2" readonly>
                                <button type="button" class="btn btn-outline-secondary plus" data-type="adult">+</button>
                            </div>
                        </div>

                        <!-- Çocuk -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Çocuk</span>
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <button type="button" class="btn btn-outline-secondary minus" data-type="child">−</button>
                                <input type="text" class="form-control text-center" data-type="child" value="0" readonly>
                                <button type="button" class="btn btn-outline-secondary plus" data-type="child">+</button>
                            </div>
                        </div>

                        <!-- Bebek -->
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Bebek</span>
                            <div class="input-group input-group-sm" style="width: 120px;">
                                <button type="button" class="btn btn-outline-secondary minus" data-type="infant">−</button>
                                <input type="text" class="form-control text-center" data-type="infant" value="0" readonly>
                                <button type="button" class="btn btn-outline-secondary plus" data-type="infant">+</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Buton -->
            <div class="col-lg-1 d-grid align-self-end">
                <button type="submit" class="btn btn-primary d-block" title="Transferleri Göster">
                    <i class="fi fi-rr-search"></i>
                </button>
            </div>
        </form>

    </div>
    {{-- Transfer Kartı --}}
    <div class="bg-white p-4 rounded shadow-sm mb-4 mt-3">
        <div class="row">
            {{-- Sol: Galeri --}}
            <div class="col-lg-5 pe-lg-5 mb-4">
                <div class="gallery">
                    {{-- Başlık --}}
                    <h4 class="fw-bold">{{ $transfer['vehicle_name'] }}</h4>
                    <p class="small text-muted">Gerek ferah ergonomik yapısı ve şekil alan koltukları ile,
                        gerekse geniş hacim ve bagaj alanı ile konforu yaşatır.</p>
                    <div
                        class="main-gallery position-relative bg-black d-flex align-items-center justify-content-center rounded mb-3"
                        style="height: 260px;">
                        @foreach ($transfer['images'] as $index => $image)
                        <img src="{{ $image }}"
                             class="gallery-image position-absolute top-0 start-0 w-100 h-100 {{ $index !== 0 ? 'd-none' : '' }}"
                             style="object-fit: contain;"
                             alt="Araç Görseli">
                        @endforeach
                    </div>
                    <div class="d-flex gap-2 overflow-auto thumbnail-scroll">
                        @foreach ($transfer['images'] as $index => $image)
                        <div class="flex-shrink-0 rounded overflow-hidden bg-black"
                             data-gallery-thumb
                             style="width: 72px; height: 72px; cursor: pointer;">
                            <img src="{{ $image }}" class="w-100 h-100" style="object-fit: cover;">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Sağ: Bilgiler + Form --}}
            <div class="col-lg-7 d-flex flex-column justify-content-between">
                <div>
                    {{-- Özet Bilgiler --}}
                    <div class="row g-3">
                        {{-- Rota --}}
                        <div class="col">
                            <div class="border rounded p-3">
                                <div class="d-block text-muted small mb-1"><i
                                        class="fi fi-rr-marker me-1 align-middle"></i>Rota
                                </div>
                                <h6 class="mb-0">{{ $transfer['from'] }} → {{ $transfer['to'] }}</h6>
                            </div>
                        </div>

                        {{-- Yolcu Sayısı --}}
                        <div class="col-lg-6">
                            <div class="border rounded p-3">
                                <div class="d-block text-muted small mb-1"><i
                                        class="fi fi-rr-user me-1 align-middle"></i>Yolcular
                                </div>
                                <h6 class="d-flex flex-wrap mb-0">
                                    @if ($transfer['adults'] > 0)
                                    <div>
                                        {{ $transfer['adults'] }} Yetişkin
                                    </div>
                                    @endif

                                    @if ($transfer['children'] > 0)
                                    <div>
                                        , {{ $transfer['children'] }} Çocuk
                                    </div>
                                    @endif

                                    @if ($transfer['infants'] > 0)
                                    <div>
                                        , {{ $transfer['infants'] }} Bebek
                                    </div>
                                    @endif
                                </h6>
                            </div>
                        </div>

                        {{-- Tarihler --}}
                        <div class="col-lg-6">
                            <div class="border rounded p-3">
                                <div class="d-block text-muted small mb-1"><i
                                        class="fi fi-rr-calendar me-1 align-middle"></i>Gidiş Tarihi
                                </div>
                                <h6 class="mb-0">
                                    {{ \Carbon\Carbon::parse($transfer['departure_date'])->translatedFormat('d F
                                    Y') }}
                                </h6>
                            </div>
                        </div>

                        @if ($transfer['direction'] === 'roundtrip' && !empty($transfer['return_date']))
                        <div class="col-lg-6">
                            <div class="border rounded p-3">
                                <div class="d-block text-muted small mb-1"><i
                                        class="fi fi-rr-calendar me-1 align-middle"></i>Dönüş
                                    Tarihi
                                </div>
                                <h6 class="mb-0">{{
                                    \Carbon\Carbon::parse($transfer['return_date'])->translatedFormat('d F
                                    Y') }} </h6>
                            </div>
                        </div>
                        @endif


                        {{-- Süre --}}
                        <div class="col-lg-6">
                            <div class="border rounded p-3">
                                <div class="d-block text-muted small mb-1"><i
                                        class="fi fi-rr-clock me-1 align-middle"></i>Süre
                                </div>
                                <h6 class="mb-0">~ {{ $transfer['estimated_duration_min'] }} dk</h6>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Rezervasyon Formu (şu an işlevsiz placeholder gibi kalacak) --}}
                <form method="POST" action="#">
                    <div class="bg-light p-3 mt-3 rounded">
                        <div class="row">
                            {{-- Saat --}}
                            <div class="col-lg-6 mb-3">
                                <label for="pickup_time" class="form-label">Gidiş Saati</label>
                                <div class="input-group">
                                    <input type="time" id="pickup_time" name="pickup_time" class="form-control">
                                    <span class="input-group-text bg-white"><i
                                            class="fi fi-rr-clock"></i></span>
                                </div>
                            </div>

                            {{-- Uçuş No --}}
                            <div class="col-lg-6 mb-3">
                                <label for="flight_number_{{ $transfer['vehicle_id'] }}" class="form-label">
                                    Gidiş Uçuş Numarası
                                </label>
                                <input type="text" class="form-control" name="flight_number"
                                       id="flight_number_{{ $transfer['vehicle_id'] }}">
                            </div>

                            {{-- Saat --}}
                            <div class="col-lg-6 mb-3">
                                <label for="pickup_time" class="form-label">Dönüş Saati</label>
                                <div class="input-group">
                                    <input type="time" id="pickup_time" name="pickup_time" class="form-control">
                                    <span class="input-group-text bg-white"><i
                                            class="fi fi-rr-clock"></i></span>
                                </div>
                            </div>

                            {{-- Uçuş No --}}
                            <div class="col-lg-6 mb-3">
                                <label for="flight_number_{{ $transfer['vehicle_id'] }}" class="form-label">
                                    Dönüş Uçuş Numarası
                                </label>
                                <input type="text" class="form-control" name="flight_number"
                                       id="flight_number_{{ $transfer['vehicle_id'] }}">
                            </div>

                            <div class="col-12">
                                <p class="text-muted small"><i class="fi fi-rr-info align-middle me-1"></i>Uçuş numaranızı girmeniz durumunda uçak saatine göre alınırsınız.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12 col-xl-6 d-grid">
                            <h2 class="m-lg-0">9.600₺</h2>
                        </div>
                        <div class="col-12 col-xl-6 d-grid">
                            <button type="submit" class="btn btn-primary">
                                Sepete Ekle
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


</section>
<section class="container">
    <div class="row">
        <div class="row align-items-center text-center mb-2">
            <!-- Metin ve Özellikler -->
            <div class="col-lg-8 offset-lg-2 text-light p-4">
                <h1 class="text-secondary display-5 fw-bold mt-3 mt-lg-2">Yolculuğunuz bizimle başlar,<br>konfor hiç bitmez.</h1>
                <p class="text-secondary">Havalimanından otelinize kadar tüm yolculuklarınızda Mercedes Vito ve
                    Sprinter araçlarımızla size özel, konforlu ve güvenli transfer hizmeti sunuyoruz.</p>
            </div>
        </div>
    </div>
    <div class="rounded" style="min-height: 500px; background-image: url('/images/transfer-bg.png'); background-repeat: no-repeat; background-position: bottom; background-size: cover">
        <div class="row">
            <div class="col-lg-6 offset-lg-3 mb-2 mt-5">
                <div class="d-flex justify-content-center gap-3 text-secondary fs-4">
                    <div class="rounded-circle bg-light p-3">
                        <i class="fi fi-sr-wifi d-block"></i>
                    </div>
                    <div class="rounded-circle bg-light p-3">
                        <i class="fi fi-sr-air-conditioner d-block"></i>
                    </div>
                    <div class="rounded-circle bg-light p-3">
                        <i class="fi fi-sr-charging-station d-block"></i>
                    </div>
                    <div class="rounded-circle bg-light p-3">
                        <i class="fi fi-sr-martini-glass-citrus d-block"></i>
                    </div>
                    <div class="rounded-circle bg-light p-3">
                        <i class="fi fi-sr-baby-carriage d-block"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="container my-5">
    <div class="row">
        <div class="col-lg-5">
            <h2 class="fw-bold mt-2 text-secondary">Öncelik güvenlik ve konfor</h2>
            <p class="text-muted mb-5">Tüm yolculuklarımızda üst düzey konfor ve güvenliği standart kabul
                ediyoruz. Araçlarımızda bulunan gelişmiş donanımlar sayesinde, havalimanından otelinize kadar
                olan her anı keyifle geçirmeniz için her şeyi düşündük.</p>
        </div>
        <div class="col-lg-7">
            <div class="row row-cols-1 text-start row-cols-md-2 g-3">
                <div class="col"><i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Derin bagaj
                    hacmi
                </div>
                <div class="col"><i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Klima, Wifi
                    ve mini buzdolabı
                </div>
                <div class="col"><i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Profesyonel
                    sürücüler
                </div>
                <div class="col"><i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Bebek koltuğu
                    opsiyonu
                </div>
                <div class="col"><i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Deri
                    koltuklar ve geniş iç hacim
                </div>
                <div class="col"><i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>USB şarj ve
                    multimedya sistemi
                </div>
                <div class="col"><i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Karartmalı
                    camlar
                </div>
                <div class="col"><i class="fi fs-5 fi-br-check me-2 text-success align-middle"></i>Özel iç
                    aydınlatma
                </div>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
