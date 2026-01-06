@extends('layouts.app')

@section('title', 'Villalar')

@section('content')

<section>
    <div class="container mt-3" style="font-size: 14px">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ localized_route('home') }}">
                        <i class="fi fi-ss-house-chimney" style="vertical-align: middle"></i>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Villalar</li>
            </ol>
        </nav>
    </div>
    <div class="text-center my-5 px-3 px-lg-5">
        {{-- PAGE HEADER (en üstteki bölüm) --}}
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

<div class="container pb-5">
    <div class="row g-4">
        @foreach($villas as $villa)

        @php
        $locale   = app()->getLocale();
        $baseLang = config('app.locale', 'tr');

        // Slug (json/jsonb)
        $slugSource = $villa['slug'] ?? null;
        if (is_array($slugSource)) {
        $slug = $slugSource[$locale] ?? ($slugSource[$baseLang] ?? reset($slugSource));
        } else {
        $slug = $slugSource;
        }

        // Name (json/jsonb)
        $nameSource = $villa['name'] ?? null;
        if (is_array($nameSource)) {
        $villaName = $nameSource[$locale] ?? ($nameSource[$baseLang] ?? reset($nameSource));
        } else {
        $villaName = $nameSource ?: 'Villa';
        }

        $city   = $villa['location']['city']   ?? null;
        $region = $villa['location']['region'] ?? null;

        $basePrice  = $villa['price']    ?? null;
        $currency   = $villa['currency'] ?? null;
        $discounted = $basePrice ? round($basePrice * 0.85) : null;
        @endphp

        <div class="col-lg-9 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-end">

                        {{-- Sol: Görsel --}}
                        <div class="col-xl-3 mb-3 mb-lg-0">
                            <a href="{{ localized_route('villa.villa-detail', ['slug' => $slug]) }}">
                                <x-responsive-image
                                    :image="$villa['cover']"
                                    preset="listing-card"
                                    class="img-fluid rounded w-100"
                                    style="height: 200px; object-fit: cover;"
                                />
                            </a>
                        </div>

                        {{-- Orta: Bilgiler --}}
                        <div class="col-xl-6 mb-3 mb-lg-0">
                            <h4 class="card-title mb-0">
                                <a href="{{ localized_route('villa.villa-detail', ['slug' => $slug]) }}"
                                   class="text-decoration-none text-dark">
                                    {{ $villaName }}
                                </a>
                            </h4>

                            <div class="text-muted small mb-3">
                                {{ $city }}
                                @if($city && $region)
                                , {{ $region }}
                                @elseif(!$city && $region)
                                {{ $region }}
                                @endif
                            </div>

                            <div class="d-flex gap-3 mb-1 text-secondary small">
                                @if(!empty($villa['max_guests']))
                                <div><i class="fi fi-rs-user align-middle"></i> {{ $villa['max_guests'] }} Kişi</div>
                                @endif
                                @if(!empty($villa['bedroom_count']))
                                <div><i class="fi fi-rs-bed-alt align-middle"></i> {{ $villa['bedroom_count'] }} Yatak Odası</div>
                                @endif
                                @if(!empty($villa['bathroom_count']))
                                <div><i class="fi fi-rs-shower align-middle"></i> {{ $villa['bathroom_count'] }} Banyo</div>
                                @endif
                            </div>

                            @if(!empty($villa['category_name']))
                            <div class="mb-3">
                            <span class="badge bg-light text-muted me-1">
                                {{ $villa['category_name'] }}
                            </span>
                            </div>
                            @endif
                        </div>

                        {{-- Sağ: Fiyat / Buton --}}
                        <div class="col-xl-3 text-xl-end">
                            <div class="d-flex flex-column">
                                <div class="text-danger small mb-1">
                                    <i class="fi fi-rs-user align-middle"></i>
                                    yeni üyelere %15 indirim!
                                </div>

                                <div>
                                    @if($basePrice)
                                    <div class="small text-muted text-decoration-line-through">
                                        {{ number_format($basePrice, 0, ',', '.') }} {{ $currency }}
                                    </div>
                                    <div class="fs-5 fw-bold text-primary">
                                        {{ number_format($discounted, 0, ',', '.') }} {{ $currency }}
                                    </div>
                                    @else
                                    <div class="text-muted small">Fiyat bilgisi bulunamadı</div>
                                    @endif
                                </div>

                                <div class="d-grid mt-1">
                                    <a href="{{ localized_route('villa.villa-detail', ['slug' => $slug]) }}"
                                       class="btn btn-outline-primary mt-auto w-100">
                                        Villayı İncele
                                    </a>
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

<div class="container mt-lg-5 mt-3">
    <div class="row">
        <div class="col-lg-6 mb-4">
            <!-- Transfer Banner -->
            <div class="mb-4 position-relative text-white rounded shadow bg-secondary rounded"
                 style="min-height: 200px;">
                <div class="position-absolute bottom-0"
                     style="right:-15px; z-index: 1; overflow: hidden; width: 320px;">
                    <img src="/images/vito.png" alt="Transfer" class="img-fluid">
                </div>
                <div class="position-relative p-4" style="z-index: 2;">
                    <h6 class="fw-light mb-0">7 Gece rezervasyonunuza</h6>
                    <h4 class="fw-bold mb-2">Ücretsiz Transfer</h4>
                    <a href="{{ localized_route('transfers') }}"
                       class="btn btn-outline-light fw-semibold mt-3 btn-sm">
                        Havaalanı Transferi
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Kampanya Banner -->
            <div class="mb-4 position-relative text-white rounded shadow bg-primary rounded"
                 style="min-height: 200px;">
                <div class="position-absolute bottom-0"
                     style="right:-15px; z-index: 1; overflow: hidden; width: 220px;">
                    <img src="/images/banner-woman.png" alt="Kampanya" class="img-fluid">
                </div>
                <div class="position-relative p-4" style="z-index: 2;">
                    <h6 class="fw-light mb-0">Hoş geldin hediyesi!</h6>
                    <h2 class="fw-bold mb-2" style="color: hotpink">%15 indirim</h2>
                    <p class="mb-3 text-shadow-transparent w-75 small">
                        İlk rezervasyonunuzda geçerli
                        <strong class="d-inline-block whitespace-nowrap">%15 indirim</strong>
                        fırsatı!
                    </p>
                    <a href="#" class="btn btn-outline-light fw-semibold btn-sm">Hesap Oluştur</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <hr class="pb-5">
    <div class="row">
        <div class="col-lg-6 pe-lg-5">
            @php
                $images = $page->villa_content_images ?? [];
                $texts  = $c['page_content']['image_texts'] ?? [];
            @endphp

            <h1 class="text-primary display-4">
                {!! nl2br(e($c['page_content']['title'][$loc] ?? '')) !!}
            </h1>

            <p class="text-secondary my-4 pe-lg-5">
                {!! nl2br(e($c['page_content']['description'][$loc] ?? '')) !!}
            </p>
        </div>
        <div class="col-lg-6">
            <div class="row">
                @for($i = 0; $i < 4; $i++)
                    @php
                        $img  = $images[$i] ?? null;
                        $text = $texts[$i][$loc] ?? null;
                    @endphp

                    <div class="col-6">
                        <div class="d-flex flex-column mb-4">
                            @if($img)
                                <x-responsive-image
                                    :image="$img"
                                    preset="listing-card"
                                    class="img-fluid rounded"
                                    width="160"
                                />
                            @endif

                            @if($text)
                                <p class="mt-2">{{ $text }}</p>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
</div>
@endsection
