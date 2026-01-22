@extends('layouts.app', ['pageKey' => 'hotel-listing'])
@section('title', 'Oteller')
@section('content')

    <section>
        <div class="text-center my-5 px-3 px-lg-5">
            @php
                $loc = app()->getLocale();
                $c = $page->content ?? [];
            @endphp

            <h1 class="display-5 fw-bold text-secondary">
                {{ $c['page_header']['title'][$loc] ?? '' }}
            </h1>

            <p class="lead text-muted px-lg-5">
                {{ $c['page_header']['description'][$loc] ?? '' }}
            </p>
        </div>
    </section>

<section class="container pb-5">
    <div class="row">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

            <!-- Filtre Menüsü Aç/Kapat -->
            <button
                type="button"
                class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
                id="toggleFilterBtn"
                aria-expanded="true"
            >
                <i class="fi fi-rr-filter"></i> {{ t('ui.filter') }}
            </button>

            <div class="d-flex align-items-center gap-2">
                <!-- Sıralama -->
                <select
                    class="form-select form-select-sm"
                    name="sort_by"
                    id="sortBySelect"
                >
                    <option value="">{{ t('ui.order') }}</option>
                    <option value="price_asc">{{ t('ui.order_price_asc') }}</option>
                    <option value="price_desc">{{ t('ui.order_price_desc') }}</option>
                    <option value="name_asc">{{ t('ui.order_name_asc') }}</option>
                    <option value="name_desc">{{ t('ui.order_name_desc') }}</option>
                </select>

            </div>
        </div>

    </div>
    <div class="row">
        <!-- Filter Sidebar -->
        <div class="col-xl-3" id="filterCol">
            @include('pages.hotel.hotel-filter')
        </div>

        <!-- Hotel Listing -->
        <div class="col-xl-9" id="listingCol">
            <div class="row g-3 hotel-list-container list-view" id="hotelList">
                @foreach ($hotels as $hotel)
                    @php
                    $locale = app()->getLocale();
                    $baseLocale = \App\Support\Helpers\LocaleHelper::defaultCode();

                    $slugSource = $hotel->slug ?? null;

                    $slug = is_array($slugSource)
                        ? \App\Support\Helpers\I18nHelper::scalar($slugSource, $locale, $baseLocale)
                        : (is_string($slugSource) ? trim($slugSource) : null);

                    if (! is_string($slug) || $slug === '') {
                        continue;
                    }

                    $hotelName = $hotel->name_l ?? $hotel->name;

                    // Yıldız (starRating ilişkisinden)
                    $stars = (int) ($hotel->starRating?->rating_value ?? 0);

                    // Lokasyon: area -> city -> region hiyerarşisi
                    $area     = $hotel->location;
                    $district = $area?->parent?->name;
                    $areaName = $area?->name;

                    $locationLabel = collect([$areaName, $district])
                        ->filter()
                        ->implode(', ');

                    // Özellik rozetleri (featureGroups üzerinden, varsa)
                    $featureGroups = $hotel->featureGroups ?? collect();
                    $allFeatures   = $featureGroups instanceof \Illuminate\Support\Collection
                    ? $featureGroups->flatMap(fn($fg) => $fg->facilities->pluck('name_l'))
                    : collect();

                    $totalFeatures   = $allFeatures->count();
                    $visibleFeatures = 5;
                @endphp

                <div class="col-12">
                    <a href="{{ localized_route('hotel.detail', ['slug' => $slug]) }}" class="card shadow-sm h-100 text-decoration-none">
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

                                {{-- Sağ: Fiyat placeholder + buton --}}
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
                                                    <span class="text-muted small d-block">{{ t('hotel_card.price_not_found') }}</span>
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
