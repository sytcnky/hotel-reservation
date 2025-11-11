@extends('layouts.app')

@section('title', 'Oteller')

@section('content')

<section>
    <div class="container mt-3" style="font-size: 14px">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#"><i class="fi fi-ss-house-chimney" style="vertical-align: middle"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Sayfa</li>
            </ol>
        </nav>
    </div>
    <div class="text-center my-5 px-lg-5">
        <h1 class="display-5 fw-bold text-secondary">En konforlu oteller</h1>
        <p class="lead text-muted px-lg-5">Birbirinden güzel otel ve size özel ayrıcalıkları keşfedin.</p>
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
                <i class="fi fi-rr-filter"></i> Filtre
            </button>


            <!-- Sıralama -->
            <div class="d-flex align-items-center gap-2">
                <!-- Sıralama -->
                <select class="form-select form-select-sm" name="sort_by" id="sortBySelect">
                    <option value="">Sıralama</option>
                    <option value="price_asc">Fiyat (Artan)</option>
                    <option value="price_desc">Fiyat (Azalan)</option>
                    <option value="name_asc">A-Z</option>
                    <option value="name_desc">Z-A</option>
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
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-end">
                                <div class="col-lg-3 mb-3 mb-lg-0">
                                    <a href="{{ localized_route('hotel.detail', ['id' => $hotel->id]) }}" class="">
                                        <img src="{{ $hotel->images[0] ?? '/images/default.jpg' }}"
                                             class="img-fluid rounded" alt="otel görseli">
                                    </a>
                                </div>
                                <div class="col-lg-6 mb-3 mb-lg-0">
                                    <h4 class="card-title mb-0">{{ $hotel->name }}</h4>
                                    <div class="mb-1 d-flex align-items-center">
                                        @for ($i = 0; $i < $hotel->stars; $i++)
                                        <i class="fi fi-ss-star text-warning"></i>
                                        @endfor
                                        @for ($i = $hotel->stars; $i < 5; $i++)
                                        <i class="fi fi-rs-star text-warning"></i>
                                        @endfor

                                        <span class="ms-1 text-secondary">{{ $hotel->board_type }}</span>
                                    </div>
                                    <span class="card-text text-muted mb-0">{{ $hotel->location->city }}, {{ $hotel->location->region }}</span>
                                    <div class="mt-2">
                                        @php
                                        $allFeatures = collect($hotel->features ?? [])
                                        ->pluck('items')
                                        ->flatten();

                                        $total = $allFeatures->count();
                                        $visible = 5;
                                        @endphp

                                        <div class="d-flex flex-wrap gap-1 mt-2">
                                            @foreach ($allFeatures->take($visible) as $feature)
                                            <span
                                                class="badge bg-transparent text-secondary border">{{ $feature }}</span>
                                            @endforeach

                                            @if ($total > $visible)
                                            <span class="badge bg-transparent    text-secondary border">+{{ $total - $visible }} daha</span>
                                            @endif
                                        </div>

                                    </div>
                                </div>

                                <div class="col-lg-3 text-lg-end">
                                    <div class="d-flex flex-column">
                                        <div class="text-danger small">
                                            <i class="fi fi-rs-user align-middle"></i> yeni üyelere 15% indirim!</span>
                                        </div>
                                        <div>
                                            @if ($firstRoom = $hotel->rooms[0] ?? null)
                                            <p><strong>{{ number_format($firstRoom->price_per_night) }}₺</strong>'den
                                                başyan fiyatlar</p>
                                            @endif
                                        </div>
                                        <div class="d-grid mt-1">
                                            <a href="{{ localized_route('hotel.detail', ['id' => $hotel->id]) }}"
                                               class="btn btn-outline-primary mt-2">Oteli İncele</a>
                                        </div>
                                    </div>
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
@endsection
