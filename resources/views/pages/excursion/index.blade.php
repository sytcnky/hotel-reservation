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

<section class="container pb-4">
    {{-- Kategori Select (Dinamik) --}}
    <div class="mb-4 col-12 col-md-6 px-0 mx-auto">
        <form method="GET" action="{{ localized_route('excursions') }}">
            <select class="form-select" name="category" onchange="this.form.submit()">
                <option value="">{{ t('ui.all_excursions') }}</option>

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

                $cover = $tour['cover'] ?? null;

                $currency = $tour['ui_currency'] ?? null;

                $adultPrice  = data_get($tour, "prices.$currency.adult");
                $childPrice  = data_get($tour, "prices.$currency.child");
                $infantPrice = data_get($tour, "prices.$currency.infant");
            @endphp

            <div class="col-12 col-md-6 col-lg-4">
                <a href="{{ localized_route('excursions.detail', ['slug' => $tour['slug']]) }}" class="card h-100 shadow-sm position-relative overflow-hidden text-decoration-none">

                    {{-- Görsel --}}
                    <x-responsive-image
                        :image="$cover"
                        preset="listing-card"
                        class="card-img-top object-fit-cover"
                        style="height: 200px;"
                    />

                    {{-- Kategori Badge --}}
                    @if (!empty($tour['category']))
                        <span class="badge bg-primary position-absolute top-0 end-0 m-2 shadow-sm">
                        {{ $tour['category'] }}
                    </span>
                    @endif

                    <div class="card-body d-flex flex-column justify-content-between">
                        <div class="mb-3">
                            <h6 class="card-title">{{ $tour['name'] }}</h6>

                            @if (!empty($tour['short_description']))
                                <p class="card-text small text-muted text-truncate-2">{{ $tour['short_description'] }}</p>
                            @endif
                        </div>
                        {{-- Fiyat Alanı --}}
                        <div class="d-flex">
                            @if($adultPrice !== null)
                                <div class="mt-auto me-3">
                                    <div class="text-muted small">{{ t('ui.adult') }}</div>
                                    <div class="fw-bold fs-6">{{ \App\Support\Currency\CurrencyPresenter::format($adultPrice, $currency) }}</div>
                                </div>
                            @endif

                            @if($childPrice !== null)
                                <div class="mt-auto me-3">
                                    <div class="text-muted small">{{ t('ui.child') }}</div>
                                    <div class="fw-bold fs-6">{{ \App\Support\Currency\CurrencyPresenter::format($childPrice, $currency) }}</div>
                                </div>
                            @endif

                            @if($infantPrice !== null)
                                <div class="mt-auto me-3">
                                    <div class="text-muted small">{{ t('ui.infant') }}</div>
                                    <div class="fw-bold fs-6">{{ \App\Support\Currency\CurrencyPresenter::format($infantPrice, $currency) }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
            </div>

        @endforeach
    </div>
</section>
@endsection
