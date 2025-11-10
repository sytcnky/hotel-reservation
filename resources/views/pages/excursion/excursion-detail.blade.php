@extends('layouts.app')

@section('title', $excursion['name'])

@section('content')
<div class="container py-5">
    <!-- Başlık ve Konum -->
    <div class="row">
        <div class="col">
            <h1 class="mb-1">{{ $excursion['name'] }}</h1>
            <!-- Etiketler -->
            <div class="mb-1">
                <span class="badge bg-primary">{{ $excursion['category'] }}</span>
                @foreach ($excursion['tags'] as $tag)
                <span class="badge bg-secondary me-1">{{ $tag }}</span>
                @endforeach
            </div>

            <p class="text-muted">{{ $excursion['location'] }}</p>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-xl-8">
            <!-- Galeri -->
            <div class="gallery">
                <div class="main-gallery position-relative mb-3 bg-black d-flex align-items-center justify-content-center rounded-3"
                     style="height: 420px;">
                    @foreach ($excursion['gallery'] as $index => $img)
                    <img src="{{ asset($img) }}"
                         class="gallery-image position-absolute top-0 start-0 w-100 h-100 {{ $index === 0 ? '' : 'd-none' }}"
                         style="object-fit: contain;"
                         data-index="{{ $index }}"
                         alt="Görsel {{ $index + 1 }}">
                    @endforeach
                </div>
                <div class="d-flex overflow-auto gap-2 pb-2 thumbnail-scroll data-gallery-thumbs">
                    @foreach ($excursion['gallery'] as $index => $img)
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

            <form onsubmit="return false;">
                <div class="card shadow-sm my-4">
                    <div class="card-body row align-items-end g-3">
                        {{-- Tarih Seçimi --}}
                        <div class="col-md-4">
                            <label for="excursion-date" class="form-label">Tarih Seçimi</label>
                            <input type="text" id="excursion-date" class="form-control" placeholder="gg.aa.yyyy" required>
                        </div>

                        <!-- Kişi Sayısı -->
                        <div class="col-md-4 guest-picker-wrapper position-relative">
                            <label for="guestInput" class="form-label">Kişi Sayısı</label>
                            <div class="input-group">
                                <input type="text"
                                       id="guestInput"
                                       class="form-control guest-wrapper"
                                       placeholder="Kişi sayısı seçin"
                                       readonly
                                       data-prices='@json($excursion["prices"])'
                                       data-currency="TRY">
                                <span class="input-group-text bg-white"><i class="fi fi-rr-user"></i></span>
                            </div>

                            <!-- Hidden inputlar -->
                            <input type="hidden" id="inputAdults" value="1">
                            <input type="hidden" id="inputChildren" value="0">
                            <input type="hidden" id="inputInfants" value="0">

                            <!-- Dropdown -->
                            <div class="guest-dropdown border rounded shadow-sm bg-white p-3 position-absolute w-100"
                                 style="z-index: 10; top: 100%; display: none;">

                                <!-- Yetişkin -->
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Yetişkin</span>
                                    <div class="input-group input-group-sm" style="width: 120px;">
                                        <button type="button" class="btn btn-outline-secondary minus" data-type="adult">−</button>
                                        <input type="text" id="adultCount" class="form-control text-center" data-type="adult" value="1" readonly>
                                        <button type="button" class="btn btn-outline-secondary plus" data-type="adult">+</button>
                                    </div>
                                </div>

                                <!-- Çocuk -->
                                @if(isset($excursion['prices']['child']['TRY']))
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Çocuk</span>
                                    <div class="input-group input-group-sm" style="width: 120px;">
                                        <button type="button" class="btn btn-outline-secondary minus" data-type="child">−</button>
                                        <input type="text" id="childCount" class="form-control text-center" data-type="child" value="0" readonly>
                                        <button type="button" class="btn btn-outline-secondary plus" data-type="child">+</button>
                                    </div>
                                </div>
                                @endif

                                <!-- Bebek -->
                                @if(isset($excursion['prices']['infant']['TRY']))
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>Bebek</span>
                                    <div class="input-group input-group-sm" style="width: 120px;">
                                        <button type="button" class="btn btn-outline-secondary minus" data-type="infant">−</button>
                                        <input type="text" id="infantCount" class="form-control text-center" data-type="infant" value="0" readonly>
                                        <button type="button" class="btn btn-outline-secondary plus" data-type="infant">+</button>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>


                        {{-- Toplam & Buton --}}
                        <div class="col-md-4 text-end">
                            <div class="fw-bold mb-0" id="excursion-price-total">
                                {{ $excursion['prices']['TRY'] ?? 0 }}₺
                            </div>
                            <button type="button" class="btn btn-primary">Sepete Ekle</button>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Tur Bilgileri -->
            <div class="bg-secondary-subtle text-dark p-3 rounded mb-4">

                <div class="row gy-3">
                    <div class="col-6 col-lg-4">
                        <strong>Fiyat:</strong><br>
                        @if (isset($excursion['prices']['adult']['TRY']))
                        {{ $excursion['prices']['adult']['TRY'] }}₺
                        @endif
                        @if (array_key_exists('child', $excursion['prices']) && isset($excursion['prices']['child']['TRY']))
                        /
                        @if ($excursion['prices']['child']['TRY'] == 0)
                        Ücretsiz
                        @else
                        {{ $excursion['prices']['child']['TRY'] }}₺
                        @endif
                        @endif

                        @if (array_key_exists('infant', $excursion['prices']) && isset($excursion['prices']['infant']['TRY']))
                        /
                        @if ($excursion['prices']['infant']['TRY'] == 0)
                        Ücretsiz
                        @else
                        {{ $excursion['prices']['infant']['TRY'] }}₺<br>
                        @endif
                        @endif
                    </div>

                    @if (!empty($excursion['duration']))
                    <div class="col-6 col-lg-4">
                        <strong>Süre:</strong><br>
                        {{ $excursion['duration'] }}
                    </div>
                    @endif

                    @if (!empty($excursion['start_time']))
                    <div class="col-6 col-lg-4">
                        <strong>Başlangıç Saati:</strong><br>
                        {{ $excursion['start_time'] }}
                    </div>
                    @endif

                    @if (!empty($excursion['days']))
                    <div class="col-6 col-lg-4">
                        <strong>Günler:</strong><br>
                        {{ implode(', ', $excursion['days']) }}
                    </div>
                    @endif

                    @if (!empty($excursion['min_age']))
                    <div class="col-6 col-lg-4">
                        <strong>Minimum Yaş:</strong><br>
                        {{ $excursion['min_age'] }}+
                    </div>
                    @endif

                    @if ($excursion['reservation_required'])
                    <div class="col-6 col-lg-4">
                        <strong>Rezervasyon:</strong><br>
                        Gerekli
                    </div>
                    @endif
                </div>
            </div>

            <!-- Açıklama -->
            <div class="mb-4">
                <p>{{ $excursion['description'] }}</p>
            </div>

            <!-- Dahil Olanlar -->
            @if (!empty($excursion['included']))
            <div class="mb-4 bg-light p-4 rounded shadow-sm">
                <h5 class="mb-3">Dahil Olanlar</h5>
                <ul class="list-unstyled mb-0">
                    @foreach ($excursion['included'] as $item)
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fi fi-ss-check-circle text-success me-2"></i>
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Dahil Olmayanlar -->
            @if (!empty($excursion['not_included']))
            <div class="mb-4 bg-light p-4 rounded shadow-sm">
                <h5 class="mb-3">Dahil Olmayanlar</h5>
                <ul class="list-unstyled mb-0">
                    @foreach ($excursion['not_included'] as $item)
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fi fi-rs-circle-x text-danger me-2"></i>
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Pickup ve Notlar -->
            @if (!empty($excursion['pickup_info']) || !empty($excursion['availability_notes']))
            <div class="mb-4 bg-light p-4 rounded shadow-sm">
                <h5 class="mb-3">Bilgilendirme</h5>
                @if (!empty($excursion['pickup_info']))
                <p class="mb-2"><strong>Transfer:</strong> {{ $excursion['pickup_info'] }}</p>
                @endif
                @if (!empty($excursion['availability_notes']))
                <p class="mb-0"><strong>Not:</strong> {{ $excursion['availability_notes'] }}</p>
                @endif
            </div>
            @endif
        </div>

        <!-- Sağ Sütun boş bırakılabilir veya transfer/kampanya eklenebilir -->
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
        </div>
    </div>
</div>
@endsection
