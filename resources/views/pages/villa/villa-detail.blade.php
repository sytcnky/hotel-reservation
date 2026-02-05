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
                            <span>{{ $villa['max_guests'] }} {{ t('ui.guests') }}</span>
                        </div>
                    @endif
                    @if(!empty($villa['bedroom_count']))
                        <div>
                            <i class="fi fi-rs-bed-alt align-middle"></i>
                            <span>{{ $villa['bedroom_count'] }} {{ t('ui.bedroom') }}</span>
                        </div>
                    @endif
                    @if(!empty($villa['bathroom_count']))
                        <div>
                            <i class="fi fi-rs-shower align-middle"></i>
                            <span>{{ $villa['bathroom_count'] }} {{ t('ui.bathroom') }}</span>
                        </div>
                    @endif
                </div>

                <!-- Konum -->
                @php
                    $area     = $villa['location']['area'] ?? null;
                    $district = $villa['location']['district'] ?? null;

                    $locationLabel = collect([$area, $district])->filter()->implode(', ');
                @endphp

                @if($locationLabel !== '')
                    <p class="text-muted">{{ $locationLabel }}</p>
                @endif
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

                        <!-- Sol Alt: Video Tanıtım Butonu -->
                        @if (!empty($villa['promo_video_id']))
                            <button type="button"
                                    class="btn btn-light d-flex align-items-center gap-2 position-absolute start-0 bottom-0 m-3 px-3 py-2 shadow-sm"
                                    style="border-radius: 999px; z-index: 5;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#villaVideoModal">
                                <i class="fi fi-rr-play text-primary"></i>
                                <span class="fw-medium text-primary small">{{ t('ui.video') }}</span>
                            </button>
                        @endif
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
                            <i class="fi fi-rr-info align-middle me-1"></i>{{ t('ui.villa.prepayment') }}
                        </small>
                    </div>
                </div>

                {{-- Rezervasyon Formu (tarih + kişi + fiyat kutusu) --}}
                @php
                    $price          = $villa['base_price'] ?? null;
                    $currency       = \App\Support\Currency\CurrencyContext::code();
                    $prepaymentRate = (float) ($villa['prepayment_rate'] ?? 0);
                    $hasPrice       = ! is_null($price) && (float) $price > 0 && ! empty($currency);

                    $currencyMeta   = $currency ? \App\Support\Currency\CurrencyPresenter::meta($currency) : null;
                    $currencyLabel  = $currency ? \App\Support\Currency\CurrencyPresenter::label($currency) : null;

                    $initialAdults   = (int) request('adults', 2);
                    $initialChildren = (int) request('children', 0);
                @endphp

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form id="villa-booking-form" method="POST"
                              action="{{ localized_route('villa.book') }}" autocomplete="off" novalidate>
                            @csrf

                            <input type="hidden" name="villa_id" value="{{ $villa['id'] }}">
                            <input type="hidden" name="checkin"  id="hidden-checkin">
                            <input type="hidden" name="checkout" id="hidden-checkout">

                            <input type="hidden" name="adults"   id="adultsInput" value="{{ $initialAdults }}">
                            <input type="hidden" name="children" id="childrenInput" value="{{ $initialChildren }}">

                            <div class="row">
                                <!-- Sol Sütun: Tarih -->
                                <div class="col-lg-4 mb-3 mb-lg-0">
                                    <label for="checkin" class="form-label">{{ t('ui.checkin_checkout_dates') }}</label>
                                    <div class="input-group">
                                        <input
                                            type="text"
                                            id="checkin"
                                            name="checkin_display"
                                            class="form-control date-input"
                                            placeholder="{{ t('ui.choose_dates') }}"
                                            autocomplete="off"
                                            data-price="{{ $hasPrice ? (float) $price : '' }}"
                                            data-prepayment-rate="{{ $prepaymentRate }}"
                                            data-unavailable="[]"
                                            required
                                        >
                                        <div class="input-group-text bg-white">
                                            <i class="fi fi-rr-calendar"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Orta Sütun: Kişi Sayısı (guestpicker) -->
                                <div class="col-lg-4 position-relative">
                                    <label for="guestInput" class="form-label">{{ t('ui.guests') }}</label>

                                    <div class="guest-picker-wrapper position-relative">
                                        <div class="input-group">
                                            <input type="text"
                                                   id="guestInput"
                                                   class="form-control guest-wrapper"
                                                   placeholder="{{ t('ui.choose_guests') }}"
                                                   readonly
                                                   data-label-adult="{{ t('ui.adult') }}"
                                                   data-label-child="{{ t('ui.child') }}"
                                                   data-placeholder="{{ t('ui.choose_guests') }}">
                                            <span class="input-group-text bg-white">
                                            <i class="fi fi-rr-user"></i>
                                        </span>
                                        </div>

                                        <!-- Dropdown -->
                                        <div class="guest-dropdown border rounded shadow-sm bg-white p-3 position-absolute w-100"
                                             style="z-index: 10; top: 100%; display: none;">

                                            <!-- Yetişkin -->
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span>{{ t('ui.adult') }}</span>
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
                                                <span>{{ t('ui.child') }}</span>
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
                                        </div>
                                    </div>
                                </div>

                                <!-- Sağ Sütun: Fiyat Kutusu -->
                                <div
                                    class="col-lg-4 mt-4 mt-lg-0 d-flex justify-content-end align-items-end text-end"
                                    id="villa-price-box"
                                    data-price="{{ $hasPrice ? (float) $price : '' }}"
                                    data-currency="{{ $currency ?? '' }}"
                                    data-currency-label="{{ $currencyLabel ?? '' }}"
                                    data-exponent="{{ (int) ($currencyMeta['exponent'] ?? 0) }}"
                                    data-affix-position="{{ $currencyMeta['affix_position'] ?? 'suffix' }}"
                                    data-prepayment="{{ $villa['prepayment_rate'] ?? 0 }}"
                                    data-min-nights="{{ $villa['min_nights'] ?? '' }}"
                                    data-max-nights="{{ $villa['max_nights'] ?? '' }}"
                                >
                                    {{-- Tarih seçilmemiş görünüm --}}
                                    <div id="price-before-selection">
                                        @if($hasPrice)
                                            <div class="fs-5 fw-semibold text-primary">
                                                {{ \App\Support\Currency\CurrencyPresenter::format($price, $currency) }}
                                                <small>/ {{ t('ui.night') }}</small>
                                            </div>
                                        @else
                                            <div class="text-muted small">
                                                {{ t('msg.info.price_not_found') }}
                                            </div>
                                        @endif

                                            <div id="min-nights-feedback" class="invalid-feedback d-block d-none">
                                                {{ t('msg.err.villa.min_nights', ['count' => $villa['min_nights']]) }}
                                            </div>

                                            <div id="max-nights-feedback" class="invalid-feedback d-block d-none">
                                                {{ t('msg.err.villa.max_nights', ['count' => $villa['max_nights']]) }}
                                            </div>
                                    </div>

                                    {{-- Tarih seçilmiş görünüm (JS sadece sayıları doldurur) --}}
                                    <div id="price-after-selection" class="d-none">
                                        <div class="small text-muted">
                                            {{ t('ui.nightly') }}:
                                            <span id="price-nightly"></span>
                                            ×
                                            <span id="price-nights"></span>
                                        </div>

                                        <div class="small fw-semibold text-danger">
                                            {{ t('ui.prepayment') }}:
                                            <span id="price-prepayment"></span>
                                        </div>

                                        <div class="fs-5 fw-bold text-primary mt-1">
                                            {{ t('ui.total_price') }}:
                                            <span id="price-total"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if(! $hasPrice)
                                <div class="alert alert-warning mt-3 mb-0">
                                    {{ t('msg.warn.villa_price_missing_for_currency') }}
                                </div>
                            @endif

                            <button type="submit"
                                    id="villaAddToCartBtn"
                                    class="btn btn-primary w-100 mt-3"
                                    @if(! $hasPrice) disabled @endif>
                                {{ t('ui.add_cart') }}
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
                @php
                    $categoryNames = array_values(array_filter(
                        $villa['category_names'] ?? [],
                        fn ($v) => is_string($v) && trim($v) !== ''
                    ));
                @endphp

                @if ($categoryNames)
                    <div class="mb-3">
                        @foreach ($categoryNames as $name)
                            <span class="badge bg-secondary me-1">{{ $name }}</span>
                        @endforeach
                    </div>
                @endif

                <!-- Öne Çıkan Özellikler -->
                @if (!empty($villa['highlights']))
                    <div class="mb-4 bg-light p-4 rounded shadow-sm" id="highlight-section">
                        <h5 class="mb-3">{{ t('ui.key_features') }}</h5>
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
                        <h5 class="mb-3">{{ t('ui.about_this_stay') }}</h5>
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

                @php
                    $hasMap = !empty($villa['latitude']) && !empty($villa['longitude']);
                    $hasNearby = !empty($villa['nearby_places']);
                @endphp

                @if($hasMap || $hasNearby)
                    <div class="bg-light p-4 rounded shadow-sm">
                        {{-- Google Maps --}}
                        @if($hasMap)
                            <div class="ratio ratio-16x9 rounded shadow-sm overflow-hidden mb-4">
                                <iframe
                                    src="https://www.google.com/maps?q={{ $villa['latitude'] }},{{ $villa['longitude'] }}&hl={{ app()->getLocale() }}&z=16&output=embed"
                                    width="100%"
                                    height="100%"
                                    style="border:0;"
                                    allowfullscreen
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade">
                                </iframe>
                            </div>
                        @endif

                        {{-- Yakındaki Yerler --}}
                        @if($hasNearby)
                            <h5 class="mt-4 mb-3">{{ t('ui.nearby_places') }}</h5>
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
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tanıtım Videosu Modalı --}}
    @if (!empty($villa['promo_video_id']))
        <div class="modal fade" id="villaVideoModal" tabindex="-1" aria-hidden="true">
            <button type="button"
                    class="btn-close btn-close-white"
                    style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 3000;
                    filter: invert(1);
                    width: 32px;
                    height: 32px;
                    opacity: .9;
                "
                    data-bs-dismiss="modal"
                    aria-label="Kapat">
            </button>

            <div class="modal-dialog modal-dialog-centered modal-xl position-relative">
                <div class="modal-content border-0 bg-black">
                    <div class="modal-body p-0">
                        <div class="ratio ratio-16x9">
                            <iframe
                                id="villaVideoFrame"
                                src="https://www.youtube.com/embed/{{ $villa['promo_video_id'] }}?modestbranding=1&rel=0&showinfo=0&iv_load_policy=3&fs=0&disablekb=1&autohide=1"
                                allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                                loading="lazy"
                                style="border:0;">
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
