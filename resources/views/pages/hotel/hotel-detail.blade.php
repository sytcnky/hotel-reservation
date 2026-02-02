@extends('layouts.app', ['pageKey' => 'hotel-details'])
@section('title', $hotel['name'])
@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col">
            <!-- Otel Başlık -->
            <h1 class="mb-1">{{ $hotel['name'] }}</h1>

            <!-- Yıldız ve Lokasyon -->
            <div class="mb-3 d-flex align-items-center gap-2">
                @if(!empty($hotel['stars']) && $hotel['stars'] > 0)
                <span>
                    @for ($i = 0; $i < $hotel['stars']; $i++)
                        <i class="fi fi-ss-star text-warning"></i>
                    @endfor
                    @for ($i = $hotel['stars']; $i < 5; $i++)
                        <i class="fi fi-rs-star text-warning"></i>
                    @endfor
                </span>
                @endif

                @php
                    $area     = $hotel['location']['area'] ?? null;
                    $district = $hotel['location']['district'] ?? null;

                    $locationLabel = collect([$area, $district])->filter()->implode(', ');
                @endphp

                @if($locationLabel !== '')
                    <span class="text-secondary">{{ $locationLabel }}</span>
                @endif

            </div>
        </div>
    </div>

    <div class="row g-5">
        <div class="col-xl-8">
            <!-- Galeri -->
            <div class="gallery">
                @php
                    $galleryImages = $hotel['hotel_gallery'] ?? [];

                    if (empty($galleryImages)) {
                        $galleryImages = [$hotel['cover']];
                    }
                @endphp
                <div
                    class="main-gallery position-relative mb-3 bg-black d-flex align-items-center justify-content-center rounded-3"
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

                    @if (!empty($hotel['promo_video_id']))
                    <!-- Sol Alt: Video Tanıtım Butonu -->
                    <button type="button"
                            class="btn btn-light d-flex align-items-center gap-2 position-absolute start-0 bottom-0 m-3 px-3 py-2 shadow-sm"
                            style="border-radius: 999px; z-index: 5;"
                            data-bs-toggle="modal"
                            data-bs-target="#hotelVideoModal">
                        <i class="fi fi-rr-play text-primary"></i>
                        <span class="fw-medium text-primary small">{{ t('ui.video') }}</span>
                    </button>
                    @endif

                </div>

                <!-- Thumbnail Satırı -->
                <div class="d-flex overflow-auto gap-2 pb-2 thumbnail-scroll data-gallery-thumbs">
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

            <!-- Form -->
            @php
            $initialAdults   = (int) ($searchParams['adults']   ?? request('adults', 2));
            $initialChildren = (int) ($searchParams['children'] ?? request('children', 0));
            $selectedBoardId = $searchParams['board_type_id']   ?? request('board_type_id');
            @endphp

            <div class="border rounded p-3 my-4">
                <form id="booking-form"
                      data-initial-adults="{{ $initialAdults }}"
                      data-initial-children="{{ $initialChildren }}"
                      class="row g-3"
                      method="GET"
                      action="{{ localized_route('hotel.detail', ['slug' => $hotel['slug']]) }}"
                      autocomplete="off"
                      novalidate>

                    <!-- Giriş-Çıkış Tarihi -->
                    <div class="col-xl-4">
                        <label for="checkin" class="form-label">{{ t('ui.checkin_checkout_dates') }}</label>
                        <div class="input-group">
                            <input type="text"
                                   id="checkin"
                                   name="checkin"
                                   class="form-control date-input"
                                   placeholder="{{ t('ui.choose_dates') }}"
                                   value="{{ request('checkin') }}"
                                   autocomplete="off"
                                   required>
                            <span class="input-group-text bg-white">
                                <i class="fi fi-rr-calendar"></i>
                            </span>
                        </div>
                    </div>

                    @if(!empty($hotel['board_types']))
                    <div class="col-xl-3">
                        <label for="boardType" class="form-label">{{ t('ui.board_type') }}</label>
                        <select name="board_type_id" class="form-select">
                            @foreach($hotel['board_types'] as $bt)
                            <option value="{{ $bt['id'] }}"
                                    @selected(!empty($selectedBoardId) && (int) $selectedBoardId === (int) $bt['id'])>
                            {{ $bt['name'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Kişi Seçimi -->
                    <div class="col-xl-4 position-relative">
                        <label for="guestInput" class="form-label">{{ t('ui.guests') }}</label>

                        <div class="guest-picker-wrapper position-relative">
                            <div class="input-group">
                                <input type="text"
                                       id="guestInput"
                                       class="form-control guest-wrapper"
                                       placeholder="{{ t('ui.guest.placeholder') }}"
                                       data-placeholder="{{ t('ui.guest.placeholder') }}"
                                       readonly
                                       data-label-adult="{{ t('ui.adult') }}"
                                       data-label-child="{{ t('ui.child') }}"
                                       data-label-infant="{{ t('ui.infant') }}">
                                <span class="input-group-text bg-white"><i class="fi fi-rr-user"></i></span>
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

                                <input type="hidden" name="adults" id="adultsInput" value="{{ $initialAdults }}">
                                <input type="hidden" name="children" id="childrenInput" value="{{ $initialChildren }}">
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-1 d-grid align-self-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fi fi-rr-search fs-5 align-middle d-none d-xl-block"></i>
                            <span class="d-xl-none ms-1">{{ t('ui.search') }}</span>
                        </button>
                    </div>
                </form>
            </div>

            @php
            $selectedBoardId   = $searchParams['board_type_id'] ?? null;
            $selectedBoardName = null;

            if ($selectedBoardId && !empty($hotel['board_types'])) {
            foreach ($hotel['board_types'] as $bt) {
            if ((int) $bt['id'] === (int) $selectedBoardId) {
            $selectedBoardName = $bt['name'];
            break;
            }
            }
            }
            @endphp


            <!-- Oda Listelemesi -->
            @foreach ($hotel['rooms'] as $room)
            @include('pages.hotel.room-card', ['room' => $room])
            @endforeach

            <!-- Otel Açıklama Metni -->
            @if(!empty($hotel['description']))
            <p class="mb-0">{!! nl2br(e($hotel['description'])) !!}</p>
            @endif
        </div>

        <div class="col-xl-4">
            <!-- Kampanya Banner -->
            @include('partials.campaigns.banner', ['campaigns' => $campaigns ?? []])

            @php
                $hasMap    = !empty($hotel['latitude']) && !empty($hotel['longitude']);
                $hasNearby = !empty($hotel['nearby']);
            @endphp

            @if($hasMap || $hasNearby)
                <div class="bg-light p-4 rounded shadow-sm">
                    @if($hasMap)
                        <div class="ratio ratio-16x9 rounded shadow-sm overflow-hidden mb-4">
                            <iframe
                                src="https://www.google.com/maps?q={{ $hotel['latitude'] }},{{ $hotel['longitude'] }}&hl={{ app()->getLocale() }}&z=16&output=embed"
                                width="100%"
                                height="100%"
                                style="border:0;"
                                allowfullscreen
                                loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                    @endif

                    @if($hasNearby)
                        <h5 class="mb-3">{{ t('ui.nearby_places') }}</h5>
                        <ul class="list-unstyled mb-0 small">
                            @foreach($hotel['nearby'] as $item)
                                <li class="mb-2 d-flex align-items-start">
                                    <i class="{{ $item['icon'] ?? 'fi fi-rr-marker' }} me-2 text-primary fs-5"></i>
                                    {{ $item['label'] }} — <strong class="ms-1">{{ $item['distance'] }}</strong>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
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
                        <span>{{ $item }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
            @endif

            <!-- Notlar -->
            @if(!empty($hotel['notes']))
            <div class="mt-4 bg-light p-4 rounded shadow-sm">
                <h5 class="mb-4">{{ t('ui.other_notes') }}</h5>
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

{{-- Tanıtım Videosu Modalı --}}
@if (!empty($hotel['promo_video_id']))
<div class="modal fade" id="hotelVideoModal" tabindex="-1" aria-hidden="true">
    {{-- Global Fixed Close Button --}}
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
                        id="hotelVideoFrame"
                        src="https://www.youtube.com/embed/{{ $hotel['promo_video_id'] }}?modestbranding=1&rel=0&showinfo=0&iv_load_policy=3&fs=0&disablekb=1&autohide=1"
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


{{-- Back --}}
<section class="container">
    <div class="row">
        <div class="col">
            <h3 class="fw-bold mb-4">
                <a href="{{ localized_route('hotels') }}"
                   class="btn btn-outline-secondary btn-sm text-decoration-none">
                    <i class="fi fi-sr-angle-square-left fs-5 align-middle"></i>
                    <span>{{ t('ui.back') }}</span>
                </a>
            </h3>
        </div>
    </div>
</section>
@endsection
