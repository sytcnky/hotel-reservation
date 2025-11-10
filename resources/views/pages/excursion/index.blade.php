@extends('layouts.app')

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
    <div class="text-center my-5 px-3 px-lg-5">
        <h1 class="display-5 fw-bold text-secondary">Günlük Turlar</h1>
        <p class="lead text-muted px-lg-5">Biletlerinizi çevrimiçi olarak ayırtın veya İçmeler gezi biletlerinizi ayırtmak için tatiliniz boyunca İçmeler ofisimizi ziyaret edin.</p>
    </div>
</section>
<section class="container py-5">
    @php
    $categories = collect($excursions)->pluck('category')->unique()->sort()->values();
    $activeCategory = request()->get('category');
    @endphp

    {{-- Kategori Pills --}}
    <div class="mb-4">
        <ul class="nav nav-pills gap-2 flex-wrap">
            <li class="nav-item">
                <a href="{{ route('excursions') }}"
                   class="nav-link {{ !$activeCategory ? 'active' : 'text-secondary' }}">
                    Tümü
                </a>
            </li>
            @foreach ($categories as $category)
            <li class="nav-item">
                <a href="{{ route('excursions', ['category' => $category]) }}"
                   class="nav-link {{ $activeCategory === $category ? 'active' : 'text-secondary' }}">
                    {{ $category }}
                </a>
            </li>
            @endforeach
        </ul>
    </div>

    {{-- Liste --}}
    <div class="row g-4">
        @foreach ($excursions as $excursion)
        @if ($activeCategory && $excursion['category'] !== $activeCategory)
        @continue
        @endif

        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm position-relative overflow-hidden">
                <div class="position-relative">
                    <a href="{{ route('excursions.detail', ['slug' => $excursion['slug']]) }}">
                        <img src="{{ asset($excursion['gallery'][0] ?? '/images/default.jpg') }}"
                             class="card-img-top object-fit-cover"
                             alt="{{ $excursion['name'] }}" height="200">
                    </a>

                    {{-- Kategori Badge --}}
                    <span class="badge bg-primary position-absolute top-0 end-0 m-2 shadow-sm">
                            {{ $excursion['category'] }}
                    </span>
                </div>

                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{{ $excursion['name'] }}</h5>
                    <p class="card-text small text-muted">{{ $excursion['short_description'] }}</p>

                    {{-- Fiyat Alanı --}}
                    <div class="mt-auto mb-3">
                        <div class="text-muted small">Kişi Başı</div>
                        <div class="fw-bold fs-5">
                            {{ $excursion['prices']['adult']['TRY'] ?? '—' }}₺
                        </div>
                    </div>

                    {{-- Butonlar --}}
                    <a href="{{ route('excursions.detail', ['slug' => $excursion['slug']]) }}"
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
