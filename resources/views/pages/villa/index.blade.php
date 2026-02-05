@extends('layouts.app')

@section('title', 'Villalar')

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

<div class="container pb-5">
    <div class="row g-4">
        @foreach($villas as $villa)

            @php
                $uiLocale   = app()->getLocale();
                $baseLocale = \App\Support\Helpers\LocaleHelper::defaultCode();

                // Slug / Name (locale-keyed map okuması tek otorite: I18nHelper)
                $slug = \App\Support\Helpers\I18nHelper::scalar($villa['slug'] ?? null, $uiLocale, $baseLocale);
                $villaName = \App\Support\Helpers\I18nHelper::scalar($villa['name'] ?? null, $uiLocale, $baseLocale);

                // Slug yoksa deterministic: kartı render etme (fallback üretmeyiz, hata da üretmeyiz)
                if (! is_string($slug) || trim($slug) === '') {
                    continue;
                }

                $city   = $villa['location']['city']   ?? null;
                $region = $villa['location']['region'] ?? null;

                $locationLabel = collect([$city, $region])->filter()->implode(', ');

                $basePrice  = $villa['price']    ?? null;
                $currency   = $villa['currency'] ?? null;
            @endphp

            <div class="col-lg-9 mx-auto">

                <a href="{{ localized_route('villa.villa-detail', ['slug' => $slug]) }}" class="card shadow-sm text-decoration-none">
                    <div class="card-body">
                        <div class="row align-items-center">
                            {{-- Sol: Görsel --}}
                            <div class="col-xl-3 mb-3 mb-lg-0">
                                <x-responsive-image
                                    :image="$villa['cover']"
                                    preset="listing-card"
                                    class="img-fluid rounded w-100"
                                    style="height: 200px; object-fit: cover;"
                                />
                            </div>

                            {{-- Orta: Bilgiler --}}
                            <div class="col-xl-6 mb-3 mb-lg-0">
                                <h4 class="card-title mb-0">
                                    {{ $villaName }}
                                </h4>

                                @if ($locationLabel)
                                    <div class="text-muted small d-flex align-items-center mb-2">
                                        <i class="fi fi-rr-marker me-1"></i>
                                        {{ $locationLabel }}
                                    </div>
                                @endif

                                <div class="d-flex gap-3 mb-1 text-secondary small">
                                    @if(!empty($villa['max_guests']))
                                    <div><i class="fi fi-rs-user align-middle"></i> {{ $villa['max_guests'] }} {{ t('ui.guests') }}</div>
                                    @endif
                                    @if(!empty($villa['bedroom_count']))
                                    <div><i class="fi fi-rs-bed-alt align-middle"></i> {{ $villa['bedroom_count'] }} {{ t('ui.bedroom') }}</div>
                                    @endif
                                    @if(!empty($villa['bathroom_count']))
                                    <div><i class="fi fi-rs-shower align-middle"></i> {{ $villa['bathroom_count'] }} {{ t('ui.bathroom') }}</div>
                                    @endif
                                </div>

                                @if (!empty($villa['category_names']) && is_array($villa['category_names']))
                                    <div class="mb-3">
                                        @foreach ($villa['category_names'] as $name)
                                            <span class="badge bg-light text-muted me-1">
                                                {{ $name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Sağ: Fiyat / Buton --}}
                            <div class="col-xl-3 text-xl-end">
                                <div>
                                    @if($basePrice)
                                        <small class="text-secondary d-block">{{ t('ui.nightly') }}</small>
                                        <div class="fs-5 fw-bold text-primary">
                                            {{ \App\Support\Currency\CurrencyPresenter::format($basePrice, $currency) }}
                                        </div>
                                    @else
                                        <div class="text-muted small">Fiyat bilgisi bulunamadı</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

        @endforeach

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
