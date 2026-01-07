@extends('layouts.app')

@section('title', 'Gezi Rehberi')

@section('content')
    <section>
        <div class="text-center my-5 px-lg-5">
            @php
                $loc = $loc ?? app()->getLocale();
                $c   = $c ?? ($page->content ?? []);
            @endphp

            <h1 class="display-5 fw-bold text-secondary">
                {{ $c['page_header']['title'][$loc] ?? '' }}
            </h1>

            <p class="lead text-muted px-lg-5">
                {{ $c['page_header']['description'][$loc] ?? '' }}
            </p>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                @foreach ($guides as $g)
                    @php
                        $title = $g->title[$locale] ?? '';
                        $excerpt = $g->excerpt[$locale] ?? '';
                        $slug = $g->slug[$locale] ?? null;

                        $cover = $g->cover_image ?? [];
                        $bg = $cover['large'] ?? '/images/samples/popular-marmaris.jpg';
                    @endphp

                    <div class="col-md-6">
                        <div
                            class="position-relative h-100 rounded overflow-hidden text-white d-flex align-items-end p-4"
                            style="background-image: url('{{ $bg }}'); background-size: cover; background-position: center;"
                        >
                            <div class="position-relative z-2">
                                <h3 class="fw-bold display-6 mt-5">{{ $title }}</h3>

                                @if($excerpt)
                                    <p class="mb-4">{{ $excerpt }}</p>
                                @else
                                    <p class="mb-4">&nbsp;</p>
                                @endif

                                <a href="{{ localized_route('guides.show', ['slug' => $slug]) }}" class="btn btn-outline-light">
                                    Gezi Rehberini İncele
                                </a>
                            </div>

                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endsection
