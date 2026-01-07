@extends('layouts.app')

@section('title', ($guide->title[$locale] ?? ($guide->title[config('app.locale','tr')] ?? 'Gezi Rehberi')))

@section('content')
    @php
        $base = config('app.locale','tr');

        $title = $guide->title[$locale] ?? ($guide->title[$base] ?? '');
        $excerpt = $guide->excerpt[$locale] ?? ($guide->excerpt[$base] ?? '');
        $tags = $guide->tags[$locale] ?? ($guide->tags[$base] ?? []);

        $cover = $guide->cover_image ?? [];
        $heroBg = $cover['large'] ?? '/images/samples/slide-summer.jpg';
    @endphp

    <div class="container mt-3" style="font-size: 14px">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
                <li class="breadcrumb-item"><a href="{{ localized_route('guides') }}">Gezi Rehberi</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
            </ol>
        </nav>
    </div>

    <section>
        <div class="container">
            <div
                class="position-relative text-white rounded overflow-hidden p-3 p-lg-5 align-content-end"
                style="background-image:url('{{ $heroBg }}');background-size:cover;background-position:center;min-height:420px;"
            >
                <div class="position-relative z-1 text-center">
                    <h1 class="display-5 fw-bold mb-1">{{ $title }}</h1>
                    @if($excerpt)
                        <p class="mb-0 text-white">{{ $excerpt }}</p>
                    @endif
                </div>
                <div class="position-absolute top-0 start-0 w-100 h-100" style="background:rgba(0,0,0,.45);"></div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row">
                {{-- SOL --}}
                <div class="col-xl-8">
                    <article class="pe-lg-5">
                        {{-- İçerik blokları --}}
                        @foreach($guide->blocks as $block)
                            @php $type = $block->type ?? null; @endphp

                            {{-- 1) Content Section --}}
                            @if($type === 'content_section')
                                @php
                                    $layout = data_get($block->data, 'layout') ?: 'stacked';
                                    $bTitle = data_get($block->data, "title.$locale") ?: data_get($block->data, "title.$base");
                                    $bBody  = data_get($block->data, "body.$locale") ?: data_get($block->data, "body.$base");

                                    $img = $block->image_asset ?? [];
                                    $imgUrl = $img['large'] ?? null;
                                @endphp

                                @if($layout === 'media_left')
                                    <div class="row mt-5 align-items-center">
                                        <div class="col-12 col-xl-6">
                                            @if($imgUrl)
                                                <div class="ratio ratio-4x3">
                                                    <img
                                                        src="{{ $imgUrl }}"
                                                        class="w-100 object-fit-cover rounded"
                                                        alt="{{ $bTitle ?: $title }}"
                                                        loading="lazy"
                                                    >
                                                </div>
                                            @endif
                                        </div>
                                        <div class="col-12 col-xl-6">
                                            @if($bTitle)<h2 class="h4">{{ $bTitle }}</h2>@endif
                                            @if($bBody)<div class="text-muted">{!! nl2br(e($bBody)) !!}</div>@endif
                                        </div>
                                    </div>
                                @else
                                    <div class="mt-5">
                                        @if($bTitle)<h2 class="h4 mb-3">{{ $bTitle }}</h2>@endif
                                        @if($bBody)<div class="text-muted">{!! nl2br(e($bBody)) !!}</div>@endif

                                        @if($imgUrl)
                                            <div class="ratio ratio-21x9 mt-3">
                                                <img
                                                    src="{{ $imgUrl }}"
                                                    class="w-100 object-fit-cover rounded"
                                                    alt="{{ $bTitle ?: $title }}"
                                                    loading="lazy"
                                                >
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            {{-- 2) Recommendation --}}
                            @if($type === 'recommendation')
                                @php
                                    $ptype = data_get($block->data, 'product_type');
                                    $pid   = (int) data_get($block->data, 'product_id');
                                @endphp

                                {{-- Hotel --}}
                                @if($ptype === 'hotel' && $pid)
                                    @php $hotel = $hotelsById->get($pid); @endphp
                                    @if($hotel)
                                        @php
                                            $hName = $hotel->name[$locale] ?? ($hotel->name[$base] ?? '');
                                            $hSlug = $hotel->slug[$locale] ?? ($hotel->slug[$base] ?? null);
                                            $hCover = $hotel->cover_image ?? [];
                                            $hCoverUrl = $hCover['thumb'] ?? '/images/default.jpg';
                                        @endphp

                                        <section class="mt-5">
                                            <h2 class="h5 mb-3">Önerilen Otel</h2>

                                            <div class="card h-100 shadow-sm">
                                                <div class="card-body p-2">
                                                    <div class="row align-items-center">
                                                        <div class="col-xl-3 mb-3 mb-lg-0">
                                                            @if($hSlug)
                                                                <a href="{{ localized_route('hotel.detail', ['slug' => $hSlug]) }}">
                                                                    <img src="{{ $hCoverUrl }}" class="img-fluid rounded" alt="otel görseli">
                                                                </a>
                                                            @else
                                                                <img src="{{ $hCoverUrl }}" class="img-fluid rounded" alt="otel görseli">
                                                            @endif
                                                        </div>

                                                        <div class="col-xl-5 mb-3 mb-lg-0">
                                                            <h4 class="card-title mb-0">{{ $hName }}</h4>

                                                            {{-- Stars + Board Type --}}
                                                            @if(isset($hotel->stars) || isset($hotel->boardType))
                                                                <div class="mb-1 d-flex align-items-center">
                                                                    @for ($i = 0; $i < ($hotel->stars ?? 0); $i++)
                                                                        <i class="fi fi-ss-star text-warning"></i>
                                                                    @endfor
                                                                    @for ($i = ($hotel->stars ?? 0); $i < 5; $i++)
                                                                        <i class="fi fi-rs-star text-warning"></i>
                                                                    @endfor

                                                                    @if($hotel->boardType?->name_l)
                                                                        <span class="ms-1 text-secondary">
                                                                            {{ $hotel->boardType->name_l }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            @endif

                                                            {{-- Location --}}
                                                            @if($hotel->location?->city || $hotel->location?->region)
                                                                <div class="text-muted small">
                                                                    {{ $hotel->location->city ?? '' }}
                                                                    {{ $hotel->location->region ? ', '.$hotel->location->region : '' }}
                                                                </div>
                                                            @endif
                                                        </div>


                                                        <div class="col-xl-4 text-lg-end">
                                                            <div class="d-grid mt-1">
                                                                @if($hSlug)
                                                                    <a href="{{ localized_route('hotel.detail', ['slug' => $hSlug]) }}" class="btn btn-outline-primary mt-2">Oteli İncele</a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    @endif
                                @endif

                                {{-- Villa --}}
                                @if($ptype === 'villa' && $pid)
                                    @php $villa = $villasById->get($pid); @endphp
                                    @if($villa)
                                        @php
                                            $vName = $villa->name[$locale] ?? ($villa->name[$base] ?? '');
                                            $vSlug = $villa->slug[$locale] ?? ($villa->slug[$base] ?? null);
                                            $vCover = $villa->cover_image ?? [];
                                            $vCoverUrl = $vCover['thumb'] ?? '/images/default.jpg';
                                        @endphp

                                        <section class="mt-4">
                                            <h2 class="h5 mb-3">Önerilen Villa</h2>

                                            <div class="card shadow-sm">
                                                <div class="card-body p-2">
                                                    <div class="row align-items-center">
                                                        <div class="col-xl-3 mb-3 mb-lg-0">
                                                            @if($vSlug)
                                                                <a href="{{ localized_route('villa.villa-detail', ['slug' => $vSlug]) }}">
                                                                    <img src="{{ $vCoverUrl }}" class="img-fluid rounded" alt="villa görseli">
                                                                </a>
                                                            @else
                                                                <img src="{{ $vCoverUrl }}" class="img-fluid rounded" alt="villa görseli">
                                                            @endif
                                                        </div>

                                                        <div class="col-xl-5 mb-3 mb-lg-0">
                                                            <h4 class="card-title mb-0">{{ $vName }}</h4>

                                                            {{-- Basic specs --}}
                                                            @if($villa->max_guests || $villa->bedroom_count || $villa->bathroom_count)
                                                                <div class="d-flex gap-3 mb-1 text-secondary">
                                                                    @if($villa->max_guests)
                                                                        <div>
                                                                            <i class="fi fi-rs-user align-middle"></i>
                                                                            <span class="small">{{ $villa->max_guests }} Kişi</span>
                                                                        </div>
                                                                    @endif

                                                                    @if($villa->bedroom_count)
                                                                        <div>
                                                                            <i class="fi fi-rs-bed-alt align-middle"></i>
                                                                            <span class="small">{{ $villa->bedroom_count }} Yatak Odası</span>
                                                                        </div>
                                                                    @endif

                                                                    @if($villa->bathroom_count)
                                                                        <div>
                                                                            <i class="fi fi-rs-shower align-middle"></i>
                                                                            <span class="small">{{ $villa->bathroom_count }} Banyo</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endif

                                                            {{-- Location --}}
                                                            @if($villa->location?->city || $villa->location?->region)
                                                                <div class="text-muted small">
                                                                    {{ $villa->location->city ?? '' }}
                                                                    {{ $villa->location->region ? ', '.$villa->location->region : '' }}
                                                                </div>
                                                            @endif
                                                        </div>


                                                        <div class="col-xl-4 text-xl-end">
                                                            <div class="d-grid mt-1">
                                                                @if($vSlug)
                                                                    <a href="{{ localized_route('villa.villa-detail', ['slug' => $vSlug]) }}" class="btn btn-outline-primary mt-auto w-100">
                                                                        Villayı İncele
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    @endif
                                @endif
                            @endif
                        @endforeach

                        {{-- Tags --}}
                        @if(is_array($tags) && count($tags))
                            <div class="d-flex flex-wrap gap-2 mt-5">
                                @foreach($tags as $tag)
                                    @if(is_string($tag) && trim($tag) !== '')
                                        <span class="badge bg-secondary">{{ $tag }}</span>
                                    @endif
                                @endforeach
                            </div>
                        @endif
                    </article>
                </div>

                {{-- SAĞ --}}
                <div class="col-xl-4 mt-5 mt-xl-0">

                    <!-- Kampanya Banner -->
                    @include('partials.campaigns.banner', ['campaigns' => $campaigns ?? []])

                    {{-- Sidebar: Popüler Turlar --}}
                    @if($sidebarTours->isNotEmpty())
                        <h4 class="text-secondary mt-5 mb-3">Bölgenin Popüler Turları</h4>

                        @foreach ($sidebarTours as $tour)
                            @php
                                $tName   = $tour->name[$locale] ?? ($tour->name[$base] ?? '');
                                $tSlug   = $tour->slug[$locale] ?? ($tour->slug[$base] ?? null);
                                $tCover  = $tour->cover_image ?? [];
                                $tImg    = $tCover['thumb'] ?? '/images/default.jpg';

                                $priceAdult = data_get($tour->prices, "{$currencyCode}.adult");
                                $priceChild = data_get($tour->prices, "{$currencyCode}.child");
                                $priceInfant = data_get($tour->prices, "{$currencyCode}.infant");
                            @endphp

                            <div class="card shadow-sm position-relative overflow-hidden mb-4">
                                {{-- Görsel --}}
                                <div class="position-relative">
                                    @if($tSlug)
                                        <a href="{{ localized_route('excursions.detail', ['slug' => $tSlug]) }}">
                                            <img
                                                src="{{ $tImg }}"
                                                class="card-img-top object-fit-cover"
                                                alt="{{ $tName }}"
                                                height="200"
                                            >
                                        </a>
                                    @else
                                        <img
                                            src="{{ $tImg }}"
                                            class="card-img-top object-fit-cover"
                                            alt="{{ $tName }}"
                                            height="200"
                                        >
                                    @endif

                                    {{-- Kategori --}}
                                    @if($tour->category?->name_l)
                                        <span class="badge bg-primary position-absolute top-0 end-0 m-2 shadow-sm">
                                            {{ $tour->category->name_l }}
                                        </span>
                                    @endif
                                </div>

                                {{-- İçerik --}}
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title mb-1">{{ $tName }}</h5>

                                    @if(!empty($tour->short_description[$locale] ?? null))
                                        <p class="card-text small text-muted">
                                            {{ $tour->short_description[$locale] }}
                                        </p>
                                    @endif

                                    {{-- Fiyatlar --}}
                                    <div class="d-flex mb-3">
                                    @if($priceAdult)
                                        <div class="mt-auto me-3">
                                            <div class="text-muted small">Yetişkin</div>
                                            <div class="fw-bold fs-5">{{ number_format($priceAdult) }} {{ $currencySymbol }}</div>
                                        </div>
                                    @endif

                                    @if($priceChild)
                                        <div class="mt-auto me-3">
                                            <div class="text-muted small">Çocuk</div>
                                            <div class="fw-bold fs-5">{{ number_format($priceChild) }} {{ $currencySymbol }}</div>
                                        </div>
                                    @endif

                                    @if($priceInfant)
                                        <div class="mt-auto me-3">
                                            <div class="text-muted small">Bebek</div>
                                            <div class="fw-bold fs-5">{{ number_format($priceInfant) }} {{ $currencySymbol }}</div>
                                        </div>
                                    @endif
                                    </div>

                                    {{-- CTA --}}
                                    @if($tSlug)
                                        <a
                                            href="{{ localized_route('excursions.detail', ['slug' => $tSlug]) }}"
                                            class="btn btn-outline-secondary btn-sm mt-auto"
                                        >
                                            Gezi Detayları ve Rezervasyon
                                        </a>
                                    @else
                                        <a href="#" class="btn btn-outline-secondary btn-sm disabled" aria-disabled="true">
                                            Gezi Detayları
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>
        </div>
    </section>
@endsection
