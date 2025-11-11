@extends('layouts.app')

@section('title', 'Villalar')

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
        <h1 class="display-5 fw-bold text-secondary">Kendini evinde hisset</h1>
        <p class="lead text-muted px-lg-5">İçmelerdeki birbirinden güzel villarda konforu ve size özel ayrıcalıkları
            keşfedin.</p>
    </div>
</section>
<div class="container pb-5">
    <div class="row g-4">
        @php
        $villas = json_decode(file_get_contents(public_path('data/villas/villas.json')), true);
        @endphp

        @foreach($villas as $villa)
        <div class="col-lg-9 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-end">
                        <!-- Sol: Görsel -->
                        <div class="col-xl-3 mb-3 mb-lg-0">
                            <a href="{{ localized_route('villa.villa-detail', ['slug' => $villa['slug']]) }}">
                                <img src="{{ asset($villa['gallery'][0] ?? '/images/default.jpg') }}"
                                     class="img-fluid rounded"
                                     alt="{{ $villa['name']['tr'] }}">
                            </a>
                        </div>

                        <!-- Orta: Bilgiler -->
                        <div class="col-xl-6 mb-3 mb-lg-0">
                            <h4 class="card-title mb-0">{{ $villa['name']['tr'] }}</h4>

                            <div class="text-muted small mb-3">
                                {{ $villa['location']['city'] }}, {{ $villa['location']['region'] }}
                            </div>

                            {{-- Temel Özellik --}}
                            <div class="d-flex gap-3 mb-1 text-secondary">
                                <div>
                                    <i class="fi fi-rs-user align-middle"></i> <span class="small">8 Kişi</span>
                                </div>
                                <div>
                                    <i class="fi fi-rs-bed-alt align-middle"></i> <span class="small">3 Yatak Odası</span>
                                </div>
                                <div>
                                    <i class="fi fi-rs-shower align-middle"></i> <span class="small">2 Banyo</span>
                                </div>
                            </div>

                            <!-- Villa Tipi Badge'leri -->
                            <div class="mb-3">
                                @foreach ($villa['types'] as $type)
                                <span class="badge bg-light text-muted me-1">{{ $type }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-xl-3 text-xl-end">
                            <div class="d-flex flex-column">
                                <div class="text-danger small">
                                    <i class="fi fi-rs-user align-middle"></i> yeni üyelere 15% indirim!</span>
                                </div>
                                <div>
                                    @php
                                    $priceTry = $villa['prices']['TRY'] ?? null;
                                    @endphp

                                    @if ($priceTry)
                                    <div class="small text-muted text-decoration-line-through">
                                        {{ number_format($priceTry, 0, ',', '.') }}₺
                                    </div>
                                    <div class="fs-5 fw-bold text-primary">
                                        {{ number_format(round($priceTry * 0.85), 0, ',', '.') }}₺ <small
                                            class="small text-secondary"></small>
                                    </div>
                                    @else
                                    <div class="text-muted small">Fiyat bilgisi bulunamadı</div>
                                    @endif
                                </div>
                                <div class="d-grid mt-1">
                                    <a href="{{ localized_route('villa.villa-detail', ['slug' => $villa['slug']]) }}"
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
                    <a href="#" class="btn btn-outline-light fw-semibold mt-3 btn-sm">Havaalanı Transferi</a>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <!-- Kampanya Banner -->
            <div class="mb-4 position-relative text-white rounded shadow bg-primary rounded" style="min-height: 200px;">
                <div class="position-absolute bottom-0" style="right:-100px; z-index: 1; overflow: hidden; width: 280px;">
                    <img src="/images/banner-woman.png" alt="Kampanya" class="img-fluid">
                </div>
                <div class="position-relative p-4" style="z-index: 2;">
                    <h6 class="fw-light mb-0">Hoş geldin hediyesi!</h6>
                    <h2 class="fw-bold mb-2" style="color: hotpink">%15 indirim</h2>
                    <p class="mb-3 text-shadow-transparent w-75 small">
                        İlk rezervasyonunuzda geçerli <strong class="d-inline-block whitespace-nowrap">%15 indirim</strong> fırsatı!
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
            <h1 class="text-primary display-4">Güzel bir tatil <span class="d-block fw-bold">
                    sözü veriyoruz.</span></h1>
            <p class="text-secondary my-4 pe-lg-5">ICR olarak sunduğumuz villa kiralama hizmetiyle, sadece konaklama değil, eksiksiz bir tatil deneyimi
                vadediyoruz. Tüm villalarımız, profesyonel ekiplerimiz tarafından yerinde incelenir ve yalnızca en
                yüksek standartları karşılayan evler sistemimize dahil edilir. Her bir villa; konfor, temizlik ve
                güvenlik kriterlerine göre özenle seçilmiştir.</p>
        </div>
        <div class="col-lg-6">
            <div class="row">
                <div class="col-6">
                    <div class="d-flex flex-column mb-4">
                        <img src="/images/samples/villa-sample-1.jpg" class="img-fluid rounded" width="160">
                        <p class="mt-2">Her ev uzmanlar tarafından incelenir, standartlarımızı karşılamaları
                            gerekir.</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex flex-column mb-4">
                        <img src="/images/samples/villa-sample-2.jpg" class="img-fluid rounded" width="160">
                        <p class="mt-2">Kusursuz derecede temiz, bakımlı evler.</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex flex-column mb-4">
                        <img src="/images/samples/villa-sample-3.jpg" class="img-fluid rounded" width="160">
                        <p class="mt-2">Tanıdığımız, yüksek kaliteli ev sahipliği geçmişi olan ev sahipleri.</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="d-flex flex-column mb-4">
                        <img src="/images/samples/villa-sample-4.jpg" class="img-fluid rounded" width="160">
                        <p class="mt-2">Nadiren de olsa ev sahibinizin iptal etmesi durumunda içinizin rahat olması.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
