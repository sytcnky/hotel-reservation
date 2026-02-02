@extends('layouts.app', ['pageKey' => 'home'])

@section('title', 'Anasayfa')

@section('content')

    @php
        $loc = app()->getLocale();
        $c = $page->content ?? [];
    @endphp

    {{-- HERO --}}
    <section class="position-relative" style="min-height: 600px;">
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
                <p class="lead text-light mb-5">{{ $c['hero']['subtitle'][$loc] ?? '' }}</p>
            </div>


            <!-- Sekmeler -->
            @include('partials.home.home-tabs', [
              'page' => $page,
              'c'    => $c,
              'loc'  => $loc,
            ])
        </div>
    </section>

    {{-- POPÜLER OTELLER --}}
    <section id="popular-hotels"
             class="container my-5"
             data-per-view="{{ (int) ($c['popular_hotels']['carousel']['per_page'] ?? 1) }}">

        <div class="row align-items-end">
            <div class="col">
                <div class="text-secondary">
                    <span class="mb-0">{{ $c['popular_hotels']['section_eyebrow'][$loc] ?? '' }}</span>
                    <h1 class="fs-3 fw-bold">{{ $c['popular_hotels']['section_title'][$loc] ?? '' }}</h1>
                </div>
            </div>

            {{-- Desktop oklar --}}
            <div class="col fs-2 text-end d-none d-lg-block">
                <button type="button"
                        class="btn btn-link p-0 fs-2 text-secondary popular-hotels-prev"
                        aria-label="Önceki">
                    <i class="fi fi-ss-arrow-circle-left"></i>
                </button>

                <button type="button"
                        class="btn btn-link p-0 fs-2 popular-hotels-next"
                        aria-label="Sonraki">
                    <i class="fi fi-ss-arrow-circle-right"></i>
                </button>
            </div>
        </div>

        <div class="row g-4 align-items-stretch">

            <!-- Sol: Bölge tanıtım alanı -->
            <div class="col-lg-5">
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
            @php
                $perPage = (int) ($c['popular_hotels']['carousel']['per_page'] ?? 3);
                $perPage = max(1, $perPage);
                $pages = $popularHotels->chunk($perPage);
            @endphp

                <!-- Sağ: Otel kartları -->
            <div class="col-lg-7">

                {{-- Mobile oklar --}}
                <div class="col fs-2 text-end d-lg-none mb-2">
                    <button type="button" class="btn btn-link p-0 fs-2 text-secondary popular-hotels-prev" aria-label="Önceki">
                        <i class="fi fi-ss-arrow-circle-left"></i>
                    </button>
                    <button type="button" class="btn btn-link p-0 fs-2 popular-hotels-next" aria-label="Sonraki">
                        <i class="fi fi-ss-arrow-circle-right"></i>
                    </button>
                </div>

                <div class="popular-hotels-viewport">
                    <div class="popular-hotels-track">
                        @foreach($pages as $pageHotels)
                            <div class="popular-hotels-page">
                                <div class="d-flex flex-column gap-3">
                                    @foreach($pageHotels as $hotel)
                                        @php $hotelName = $pickLocale($hotel->name) ?? ''; @endphp

                                        @php
                                            $hotelName = $pickLocale($hotel->name) ?? '';
                                            $slug = $pickLocale($hotel->slug) ?? '';

                                            $stars = (int) ($hotel->starRating?->value ?? 0);

                                            $areaName  = $hotel->location ? ($pickLocale($hotel->location->name) ?? null) : null;
                                            $district  = $hotel->location?->parent ? ($pickLocale($hotel->location->parent->name) ?? null) : null;

                                            $locationLabel = collect([$areaName, $district])
                                                ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                                                ->implode(', ');

                                            // özellikler (listing ile aynı mantık)
                                            $allFeatures = collect();

                                            if ($hotel->relationLoaded('featureGroups')) {
                                                $allFeatures = $hotel->featureGroups
                                                    ->flatMap(fn ($g) => $g->facilities?->pluck('name') ?? collect())
                                                    ->map(fn ($m) => $pickLocale($m) ?? null)
                                                    ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                                                    ->values();
                                            }

                                            $visibleFeatures = 4;
                                            $totalFeatures   = $allFeatures->count();
                                        @endphp

                                        <a href="{{ $slug !== '' ? route(app()->getLocale().'.hotel.detail', ['slug' => $slug]) : '#' }}" class="card shadow-sm h-100 text-decoration-none">
                                            <div class="card-body">
                                                <div class="row align-items-center">

                                                    {{-- Sol: Görsel --}}
                                                    <div class="col-lg-3 mb-3 mb-lg-0">
                                                        <x-responsive-image
                                                            :image="$hotel->cover_image"
                                                            preset="listing-card"
                                                            class="rounded object-fit-cover w-100"
                                                        />
                                                    </div>

                                                    {{-- Orta: Başlık, yıldız, konum, özellikler --}}
                                                    <div class="col-lg-6 mb-3 mb-lg-0">
                                                        <h4 class="card-title mb-1">
                                                            {{ $hotelName }}
                                                        </h4>

                                                        @if($stars > 0)
                                                            <div class="mb-1 d-flex align-items-center">
                                                                @for ($i = 0; $i < $stars; $i++)
                                                                    <i class="fi fi-ss-star text-warning"></i>
                                                                @endfor
                                                                @for ($i = $stars; $i < 5; $i++)
                                                                    <i class="fi fi-rs-star text-warning"></i>
                                                                @endfor
                                                            </div>
                                                        @endif

                                                        @if ($locationLabel)
                                                            <div class="text-muted small d-flex align-items-center">
                                                                <i class="fi fi-rr-marker me-1"></i>
                                                                {{ $locationLabel }}
                                                            </div>
                                                        @endif

                                                        @if ($totalFeatures > 0)
                                                            <div class="d-flex flex-wrap gap-1 mt-2">
                                                                @foreach ($allFeatures->take($visibleFeatures) as $feature)
                                                                    <span class="badge bg-transparent text-secondary border">
                                                                        {{ $feature }}
                                                                    </span>
                                                                @endforeach

                                                                @if ($totalFeatures > $visibleFeatures)
                                                                    <span class="badge bg-transparent text-secondary border">
                                                                        +{{ $totalFeatures - $visibleFeatures }} {{ t('hotel_card.more') }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- Sağ: Fiyat + buton --}}
                                                    <div class="col-lg-3 text-lg-end">
                                                        <div class="d-flex flex-column align-items-lg-end">
                                                            <div class="mb-2">
                                                                @php
                                                                    $amount = $hotel->from_price_amount ?? null;
                                                                    $type   = $hotel->from_price_type ?? null;

                                                                    $suffix = match ($type) {
                                                                        'room_per_night'   => '/ ' . t('hotel_card.room'),
                                                                        'person_per_night' => '/ ' . t('hotel_card.person'),
                                                                        default            => '',
                                                                    };
                                                                @endphp

                                                                @if(!is_null($amount))
                                                                    <div class="mb-2">
                                                                        <div class="fw-semibold fs-5">
                                                                            {{ \App\Support\Currency\CurrencyPresenter::format($amount, $currencyCode ?? null) }}
                                                                            <span class="text-muted small">{{ $suffix }}</span>
                                                                        </div>
                                                                        <span class="text-muted small d-block">{{ t('hotel_card.prices_starting_from') }}</span>
                                                                    </div>
                                                                @else
                                                                    <div class="mb-2">
                                                                        <span class="text-muted small d-block">{{ t('msg.info.price_not_found') }}</span>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>


        </div>
    </section>


    <!-- Kampanya Carousel -->
    @include('partials.campaigns.carousel', ['campaigns' => $campaigns ?? []])

    {{-- GEZİ REHBERİ --}}
    <section class="container my-5">
        <div class="row">
            <div class="col-12 col-xl-4 text-secondary">
                <h1 class="display-1 lh-1 mb-5">{{ $c['travel_guides']['hero_title'][$loc] ?? '' }}</h1>
                <h2 class="fs-3 fw-bold mb-5">{{ $c['travel_guides']['title'][$loc] ?? '' }}</h2>

                <p>{!! nl2br(e($c['travel_guides']['description'][$loc] ?? '')) !!}</p>

                <a href="{{ localized_route('guides') }}" class="btn btn-outline-secondary btn-sm">{{ t('nav.guides') }}</a>
            </div>

            <div class="col-12 col-xl-8 ps-xl-5">
                <div class="row g-4">
                    @foreach($travelGuides as $guide)
                        @php
                            $guideTitle = $pickLocale($guide->title) ?? '';

                            // locale'e göre slug
                            $guideSlug = $pickLocale($guide->slug) ?? '';

                            // localized url (slug yoksa link devre dışı)
                            $href = $guideSlug !== ''
                                ? localized_route('guides.show', ['slug' => $guideSlug])
                                : '#';
                        @endphp

                        <div class="col-xl-6">
                            <a href="{{ $href }}"
                               class="position-relative text-light text-decoration-none h-100 rounded overflow-hidden d-flex align-items-end p-4"
                               style="min-height: 240px">

                                <x-responsive-image
                                    :image="$guide->cover_image"
                                    preset="listing-card"
                                    class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover"
                                />

                                <div class="position-relative z-2">
                                    <h3 class="fw-bold m-0">{{ $guideTitle }}</h3>
                                </div>

                                <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
