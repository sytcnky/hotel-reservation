@extends('layouts.app')

@section('content')

<section>
    <div class="text-center my-5 px-3 px-lg-5">
        @php
            $loc = $loc ?? app()->getLocale();
            $c = $c ?? ($page->content ?? []);
        @endphp

        <h1 class="display-5 fw-bold text-secondary">
            {{ $c['page_header']['title'][$loc] ?? '' }}
        </h1>

        <p class="lead text-muted px-lg-5">
            {{ $c['page_header']['description'][$loc] ?? '' }}
        </p>
    </div>
</section>

<section class="container py-4">

    {{-- Kategori Select (Dinamik) --}}
    <div class="mb-4 col-12 col-md-4 px-0">
        <form method="GET" action="{{ localized_route('excursions') }}">
            <select class="form-select" name="category" onchange="this.form.submit()">
                <option value="">Tümü</option>

                @foreach ($categories as $cat)
                <option value="{{ $cat['slug'] }}"
                        {{ request('category') === $cat['slug'] ? 'selected' : '' }}>
                {{ $cat['name'] }}
                </option>
                @endforeach

            </select>
        </form>
    </div>

    {{-- Liste --}}
    <div class="row g-4">

        @foreach ($tours as $tour)

        @php
            $activeCategory = request('category');

        if ($activeCategory && $tour['category_slug'] !== $activeCategory) {
            continue;
        }

        $prices   = $tour['prices'] ?? [];
        $currency = \App\Support\Helpers\CurrencyHelper::currentCode();
        $currency = strtoupper($currency);

        $adultPrice = null;

        if (isset($prices[$currency]['adult'])) {
            $adultPrice = $prices[$currency]['adult'];
        } elseif (isset($prices['TRY']['adult'])) {
            $adultPrice = $prices['TRY']['adult'];
            $currency   = 'TRY';
        } elseif (!empty($prices)) {
            $firstCurrency = array_key_first($prices);
            $adultPrice    = $prices[$firstCurrency]['adult'] ?? null;
            $currency      = $firstCurrency;
        }

        $cover = $tour['cover'] ?? null;
        @endphp

        <div class="col-sm-6 col-lg-4">
            <div class="card h-100 shadow-sm position-relative overflow-hidden">

                {{-- Görsel --}}
                <a href="{{ localized_route('excursions.detail', ['slug' => $tour['slug']]) }}">
                    <x-responsive-image
                        :image="$cover"
                        preset="listing-card"
                        class="card-img-top object-fit-cover"
                        style="height: 200px;"
                    />
                </a>

                {{-- Kategori Badge --}}
                @if (!empty($tour['category']))
                <span class="badge bg-primary position-absolute top-0 end-0 m-2 shadow-sm">
                            {{ $tour['category'] }}
                        </span>
                @endif

                <div class="card-body d-flex flex-column">

                    <h5 class="card-title">{{ $tour['name'] }}</h5>

                    @if (!empty($tour['short_description']))
                    <p class="card-text small text-muted">{{ $tour['short_description'] }}</p>
                    @endif

                    {{-- Fiyat Alanı --}}
                    <div class="mt-auto mb-3">
                        <div class="text-muted small">Kişi Başı</div>

                        @if ($adultPrice !== null)
                        <div class="fw-bold fs-5">
                            {{ number_format($adultPrice, 0, ',', '.') }} {{ $currency }}
                        </div>
                        @else
                        <div class="fw-bold fs-6 text-muted">Fiyat bilgisi yok</div>
                        @endif
                    </div>

                    <a href="{{ localized_route('excursions.detail', ['slug' => $tour['slug']]) }}"
                       class="btn btn-outline-secondary">
                        Gezi Detayları ve Rezervasyon
                    </a>

                </div>
            </div>
        </div>
        @endforeach
    </div>
</section>
@endsection
