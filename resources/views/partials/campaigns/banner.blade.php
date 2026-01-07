@props([
    'campaigns' => [],
])

@php
    $items = is_array($campaigns) ? $campaigns : ($campaigns?->toArray() ?? []);
@endphp

@if(!empty($items))
    @foreach($items as $c)
        @php
            $trImg = $c['transparent_image'] ?? null;
            $hasTr = is_array($trImg) && ($trImg['exists'] ?? false);

            $ctaText = $c['cta_text'] ?? null;
            $ctaLink = $c['cta_link'] ?? null;
            $hasCta  = $ctaText && $ctaLink;

            $bgClass = $c['background_class'] ?? 'bg-primary';
        @endphp

        <div class="mb-4 position-relative text-white rounded shadow {{ $bgClass }} rounded" style="max-height: 200px;">

            <div class="position-absolute bottom-0"
                 style="right:-15px; z-index: 1; overflow: hidden; width: 180px;">
                <!-- Görsel -->
                @if($hasTr)
                    <x-responsive-image
                        :image="$trImg"
                        preset="listing-card"
                        class="img-fluid"
                    />
                @endif
            </div>

            <!-- İçerik -->
            <div class="position-relative p-4" style="z-index: 2;">
                <h6 class="fw-light mb-0">{{ $c['subtitle'] ?? '' }}</h6>
                <h2 class="fw-bold mb-2">{{ $c['title'] ?? '' }}</h2>

                <p class="mb-3 text-shadow-transparent w-75 small">
                    {{ $c['description'] ?? '' }}
                </p>

                @if($hasCta)
                    <a href="{{ $ctaLink }}" class="btn btn-outline-light fw-semibold btn-sm">
                        {{ $ctaText }}
                    </a>
                @endif
            </div>
        </div>
    @endforeach
@endif
