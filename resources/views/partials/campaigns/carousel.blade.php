@props([
    'campaigns' => [],
    'id' => 'heroCarousel',
])

@php
    $items = is_array($campaigns) ? $campaigns : ($campaigns?->toArray() ?? []);
@endphp

@if(!empty($items))
    {{-- KAMPANYA CAROUSEL --}}
    <div class="container mt-5">
        <section id="{{ $id }}" class="carousel slide" data-bs-ride="carousel">
            <!-- Carousel Items -->
            <div class="carousel-inner rounded">
                @foreach($items as $i => $c)
                    @php
                        $isActive = $i === 0 ? 'active' : '';
                        $bgImg = $c['background_image'] ?? null;
                        $hasBg = is_array($bgImg) && ($bgImg['exists'] ?? false);

                        $ctaText = $c['cta_text'] ?? null;
                        $ctaLink = $c['cta_link'] ?? null;
                        $hasCta  = $ctaText && $ctaLink;
                    @endphp

                    <div class="carousel-item {{ $isActive }}">
                        <div class="hero-slide position-relative p-5 overflow-hidden">
                            @if($hasBg)
                                {{-- Arka plan görsel --}}
                                <x-responsive-image
                                    :image="$bgImg"
                                    preset="gallery"
                                    sizes="100vw"
                                    class="position-absolute top-0 start-0 w-100 h-100 object-fit-cover"
                                />
                            @endif
                            <div class="d-flex flex-column flex-lg-row position-relative z-3 text-white gap-4">
                                <div class="flex-lg-grow-1">
                                    <h3 class="fw-bold mb-3 te">{{ $c['subtitle'] ?? '' }}</h3>
                                    <div class="display-1">{{ $c['title'] ?? '' }}</div>
                                    <p class="lead mb-4">{{ $c['description'] ?? '' }}</p>
                                </div>

                                @if($hasCta)
                                    <div>
                                        <div class="d-grid">
                                            <a href="{{ $ctaLink }}" class="btn btn-outline-light btn-lg px-2 mb-4 px-lg-5">
                                                {{ $ctaText }}
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="overlay z-1 bg-dark opacity-50"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(count($items) > 1)
                <!-- Carousel Indicators -->
                <div class="carousel-indicators position-absolute bottom-0 mb-4">
                    @foreach($items as $i => $_)
                        <button
                            type="button"
                            data-bs-target="#{{ $id }}"
                            data-bs-slide-to="{{ $i }}"
                            class="{{ $i === 0 ? 'active' : '' }}"
                            @if($i === 0) aria-current="true" @endif
                            aria-label="Slide {{ $i + 1 }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endif
