@extends('layouts.app')

@section('title', 'Gezi Rehberi')

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
        <h1 class="display-5 fw-bold text-secondary">Gezi Rehberi</h1>
        <p class="lead text-muted px-lg-5">Bölgeler, gezilecek yerler ve ipuçları — keşfe başlayın.</p>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            @foreach ($guides as $g)
            <div class="col-md-6">
                <div class="position-relative h-100 rounded overflow-hidden text-white d-flex align-items-end p-4" style="background-image: url('/images/samples/popular-marmaris.jpg'); background-size: cover; background-position: center;">
                    <div class="position-relative z-2">
                        <h3 class="fw-bold display-5 mt-5">{{ $g['title'] }}</h3>
                        <p class="mb-4">Marmaris’te gezilecek yerler, plajlar, yeme-içme ve turlar...</p>
                        <a href="{{ route('guides.show', $g['slug']) }}" class="btn btn-outline-light">{{ $g['title'] }} Gezi Rehberi</a>
                    </div>
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
