@extends('layouts.app')

@section('title', $tour['name'])

@section('content')
<div class="container py-5">
    {{-- Başlık ve kategori --}}
    <div class="row">
        <div class="col">
            <h1 class="mb-1">{{ $tour['name'] }}</h1>

            <div class="mb-1">
                @if (!empty($tour['category_name']))
                <span class="badge bg-primary">{{ $tour['category_name'] }}</span>
                @endif
            </div>

            {{-- Konum bilgisini şu an modelde tutmuyoruz; istersen ekleriz --}}
        </div>
    </div>

    <div class="row g-5">
        <div class="col-xl-8">
            {{-- Galeri --}}
            <div class="gallery">
                <div class="main-gallery position-relative mb-3 bg-black d-flex align-items-center justify-content-center rounded-3"
                     style="height: 420px;">
                    @foreach ($tour['gallery'] as $index => $img)
                    <img src="{{ $img['large'] }}"
                         srcset="{{ $img['large'] }} 1x, {{ $img['large2x'] }} 2x"
                         class="gallery-image position-absolute top-0 start-0 w-100 h-100 {{ $index === 0 ? '' : 'd-none' }}"
                         style="object-fit: contain;"
                         data-index="{{ $index }}"
                         alt="{{ $img['alt'] }}">
                    @endforeach
                </div>

                <div class="d-flex overflow-auto gap-2 pb-2 thumbnail-scroll" data-gallery-thumbs>
                    @foreach ($tour['gallery'] as $index => $img)
                    <div class="flex-shrink-0 overflow-hidden bg-black rounded"
                         style="width: 92px; height: 92px; cursor: pointer;"
                         data-gallery-thumb>
                        <img src="{{ $img['small'] }}"
                             srcset="{{ $img['small'] }} 1x, {{ $img['small2x'] }} 2x"
                             class="w-100 h-100"
                             style="object-fit: cover; object-position: center;"
                             alt="{{ $img['alt'] }}">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Rezervasyon formu --}}
            @php
            $prices    = $tour['prices'] ?? [];
            $currency  = strtoupper($currency ?? 'TRY');
            $adultBase = $prices[$currency]['adult'] ?? null;
            @endphp

            <form id="excursionForm"
                  method="POST"
                  action="{{ localized_route('tour.book') }}"
                  novalidate
                  data-days="{{ implode(',', $tour['days_of_week'] ?? []) }}">
            @csrf

            {{-- BE için temel alanlar --}}
            <input type="hidden" name="tour_id" value="{{ $tour['id'] }}">
            <input type="hidden" name="tour_name" value="{{ $tour['name'] }}">
            <input type="hidden" name="currency" value="{{ $currency }}">
            <input type="hidden" id="inputTotal" name="price_total" value="{{ $adultBase ?? 0 }}">

                @if(!empty($tour['cover']))
                <input type="hidden" name="cover_image[thumb]" value="{{ $tour['cover']['thumb'] }}">
                <input type="hidden" name="cover_image[thumb2x]" value="{{ $tour['cover']['thumb2x'] }}">
                <input type="hidden" name="cover_image[alt]" value="{{ $tour['cover']['alt'] }}">
                @endif

                @if(!empty($tour['category_name']))
                <input type="hidden" name="category_name" value="{{ $tour['category_name'] }}">
                @endif

            {{-- Kişi sayıları (hidden) --}}
                <input type="hidden" id="inputAdults"   name="adults"   value="1">
                <input type="hidden" id="inputChildren" name="children" value="0">
                <input type="hidden" id="inputInfants"  name="infants"  value="0">

            <div class="card shadow-sm my-4">
                <div class="card-body row align-items-end g-3">

                    {{-- Tarih --}}
                    <div class="col-md-4">
                        <label for="excursion-date" class="form-label">Tarih Seçimi</label>

                        <input type="text"
                               id="excursion-date"
                               name="date"
                               class="form-control"
                               placeholder="gg.aa.yyyy"
                               required>
                    </div>

                    {{-- Kişi Sayısı --}}
                    <div class="col-md-4 guest-picker-wrapper position-relative">
                        <label for="guestInput" class="form-label">Kişi Sayısı</label>

                        <div class="input-group">
                            <input type="text"
                                   id="guestInput"
                                   class="form-control guest-wrapper"
                                   placeholder="Kişi sayısı seçin"
                                   readonly
                                   data-prices='@json($prices)'
                                   data-currency="{{ $currency }}">
                            <span class="input-group-text bg-white">
            <i class="fi fi-rr-user"></i>
        </span>
                        </div>

                        {{-- Dropdown --}}
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
                                           value="1"
                                           readonly>
                                    <button type="button"
                                            class="btn btn-outline-secondary plus"
                                            data-type="adult">+</button>
                                </div>
                            </div>

                            {{-- Çocuk --}}
                            @if(isset($prices[$currency]['child']))
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
                                           value="0"
                                           readonly>
                                    <button type="button"
                                            class="btn btn-outline-secondary plus"
                                            data-type="child">+</button>
                                </div>
                            </div>
                            @endif

                            {{-- Bebek --}}
                            @if(isset($prices[$currency]['infant']))
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
                                           value="0"
                                           readonly>
                                    <button type="button"
                                            class="btn btn-outline-secondary plus"
                                            data-type="infant">+</button>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Toplam & Buton --}}
                    <div class="col-md-4 text-end">
                        <div class="fw-bold mb-0" id="excursion-price-total">
                            @if ($adultBase !== null)
                            {{ number_format($adultBase, 0, ',', '.') }} {{ $currency }}
                            @else
                            —
                            @endif
                        </div>

                        <button type="submit"
                                id="btnExcursionAddToCart"
                                class="btn btn-primary mt-2">
                            Sepete Ekle
                        </button>
                    </div>
                </div>
            </div>
            </form>

            {{-- Tur Bilgileri --}}
            <div class="bg-secondary-subtle text-dark p-3 rounded mb-4">
                <div class="row gy-3">
                    {{-- Fiyat --}}
                    <div class="col-6 col-lg-4">
                        <strong>Fiyat (Yetişkin):</strong><br>
                        @if ($adultBase !== null)
                        {{ number_format($adultBase, 0, ',', '.') }} {{ $currency }}
                        @else
                        —
                        @endif

                        @php
                        $childBase  = $prices[$currency]['child']  ?? null;
                        $infantBase = $prices[$currency]['infant'] ?? null;
                        @endphp

                        @if ($childBase !== null)
                        <br><span class="small text-muted">Çocuk: {{ $childBase == 0 ? 'Ücretsiz' : number_format($childBase, 0, ',', '.') . ' ' . $currency }}</span>
                        @endif

                        @if ($infantBase !== null)
                        <br><span class="small text-muted">Bebek: {{ $infantBase == 0 ? 'Ücretsiz' : number_format($infantBase, 0, ',', '.') . ' ' . $currency }}</span>
                        @endif
                    </div>

                    @if (!empty($tour['duration']))
                    <div class="col-6 col-lg-4">
                        <strong>Süre:</strong><br>
                        {{ $tour['duration'] }}
                    </div>
                    @endif

                    @if (!empty($tour['start_time']))
                    <div class="col-6 col-lg-4">
                        <strong>Başlangıç Saati:</strong><br>
                        {{ $tour['start_time'] }}
                    </div>
                    @endif

                    @if (!empty($tour['days_of_week']))
                    <div class="col-6 col-lg-4">
                        <strong>Günler:</strong><br>
                        {{ collect($tour['days_of_week'])->map(fn($d) => t('weekdays.' . $d))->join(', ') }}
                    </div>
                    @endif

                    @if (!empty($tour['min_age']))
                    <div class="col-6 col-lg-4">
                        <strong>Minimum Yaş:</strong><br>
                        {{ $tour['min_age'] }}+
                    </div>
                    @endif
                </div>
            </div>

            {{-- Açıklama --}}
            @if (!empty($tour['long_description']))
            <div class="mb-4">
                {!! nl2br(e($tour['long_description'])) !!}
            </div>
            @elseif (!empty($tour['short_description']))
            <div class="mb-4">
                {!! nl2br(e($tour['short_description'])) !!}
            </div>
            @endif

            {{-- Dahil Olanlar --}}
            @if (!empty($tour['included_services']))
            <div class="mb-4 bg-light p-4 rounded shadow-sm">
                <h5 class="mb-3">Dahil Olanlar</h5>
                <ul class="list-unstyled mb-0">
                    @foreach ($tour['included_services'] as $item)
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fi fi-ss-check-circle text-success me-2"></i>
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Dahil Olmayanlar --}}
            @if (!empty($tour['excluded_services']))
            <div class="mb-4 bg-light p-4 rounded shadow-sm">
                <h5 class="mb-3">Dahil Olmayanlar</h5>
                <ul class="list-unstyled mb-0">
                    @foreach ($tour['excluded_services'] as $item)
                    <li class="mb-2 d-flex align-items-center">
                        <i class="fi fi-rs-circle-x text-danger me-2"></i>
                        <span>{{ $item }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        {{-- Sağ sütun: kampanya / transfer banner’ları aynen kalsın --}}
        <div class="col-xl-4">
            <div class="mb-4 position-relative text-white rounded shadow bg-primary" style="min-height: 200px;">
                <div class="position-absolute bottom-0" style="right:-100px; z-index: 1; overflow: hidden; width: 280px;">
                    <img src="/images/banner-woman.png" alt="Kampanya" class="img-fluid">
                </div>
                <div class="position-relative p-4" style="z-index: 2;">
                    <h6 class="fw-light mb-0">Hoş geldin hediyesi!</h6>
                    <h2 class="fw-bold mb-2" style="color: hotpink">%15 indirim</h2>
                    <p class="mb-3 w-75 small">
                        İlk rezervasyonunuzda geçerli <strong class="d-inline-block">%15 indirim</strong> fırsatı!
                    </p>
                    <a href="#" class="btn btn-outline-light fw-semibold btn-sm">Hesap Oluştur</a>
                </div>
            </div>

            <div class="mb-4 position-relative text-white rounded shadow bg-secondary" style="min-height: 160px;">
                <div class="position-absolute bottom-0" style="right:-15px; z-index: 1; overflow: hidden; width: 220px;">
                    <img src="/images/vito.png" alt="Transfer" class="img-fluid">
                </div>
                <div class="position-relative p-4" style="z-index: 2;">
                    <h6 class="fw-light mb-0">7 Gece rezervasyonunuza</h6>
                    <h4 class="fw-bold mb-2">Ücretsiz Transfer</h4>
                    <a href="{{ localized_route('transfers') }}" class="btn btn-outline-light fw-semibold mt-3 btn-sm">
                        Havaalanı Transferi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const form      = document.getElementById('excursionForm');
        const dateInput = document.getElementById('excursion-date');

        if (!form || !dateInput) {
            return;
        }

        // Submit anında tarih kontrolü
        form.addEventListener('submit', function (event) {
            const value = dateInput.value.trim();

            if (!value) {
                // Tarih seçilmemiş → submit iptal
                event.preventDefault();
                event.stopPropagation();

                dateInput.classList.add('is-invalid');
                form.classList.add('was-validated');
                return;
            }

            // Tarih doluysa hata durumunu temizle
            dateInput.classList.remove('is-invalid');
            form.classList.add('was-validated');
        });

        // Flatpickr + manuel yazma için hem input hem change event'lerine bağlan
        ['input', 'change'].forEach(function (evt) {
            dateInput.addEventListener(evt, function () {
                if (dateInput.value.trim() !== '') {
                    dateInput.classList.remove('is-invalid');
                }
            });
        });
    })();
</script>

@endsection
