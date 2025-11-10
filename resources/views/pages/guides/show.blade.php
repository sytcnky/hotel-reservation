@extends('layouts.app') @section('title', $guide['title']) @section('content')
<div class="container mt-3" style="font-size: 14px">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Anasayfa</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Gezi Rehberi</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sedir Adası</li>
        </ol>
    </nav>
</div>
<section>
    <div class="container">
        <div class="position-relative text-white rounded overflow-hidden p-3 p-lg-5 align-content-end"
             style="background-image:url('/images/samples/slide-summer.jpg');background-size:cover;background-position:center;min-height:420px;">
            <div class="position-relative z-1 text-center"><h1 class="display-5 fw-bold mb-1">Sedir Adası</h1>
                <p class="mb-0 text-white">Altın renkli kumları, berrak koyları ve farklı medeniyetlere ev sahipliği
                    yapmış antik kentleri ile Sedir Adası</p></div>
            <div class="position-absolute top-0 start-0 w-100 h-100" style="background:rgba(0,0,0,.45);"></div>
        </div>
    </div>
</section>
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-xl-8">
                <article class="pe-lg-5"> {{-- İçerik gövdesi: şimdilik placeholder; bölüm bölüm ilerleyeceğiz --}} <h2
                        class="h4 mb-3">Genel Bakış</h2>
                    <p class="text-muted">Marmaris, Türkiye'nin en popüler tatil beldeleri arasında yer alır ve yeşil ve
                        mavi doğanın muhteşem birlikteliğini sunmasıyla dikkat çeker. Gökova Körfezi'nde bulunan Sedir
                        Adası, diğer adıyla Kleopatra Adası, turkuaz mavisi denizi ve beyaz ince kumlu plajlarıyla
                        dikkat çeken özellikleriyle Maldivleri aratmayacak türden. Bu sebeplerle Marmaris, Türkiye'nin
                        görülmeye değer tatil destinasyonlarından.</p>
                    <div class="ratio ratio-21x9"><img src="/images/samples/popular-marmaris.jpg"
                                                       class="w-100 object-fit-cover rounded"
                                                       alt="{{ $guide['title'] }}" loading="lazy"></div>
                    <h2 class="h4 mt-5 mb-3">Sedir Adası’nın tarihi</h2>
                    <p class="text-muted">Sedir Adası, Cedrae olarak da bilinir ve adını Sedir Ağacından (Cedrus) alır.
                        Bugün adada sadece çam ve zeytin ağaçları bulunuyor. Adada Helenistik-Roma dönemine ait
                        kalıntıları görebilirsiniz. Adanın tarihi, Peleponnesos Savaşı sonrasında Spartalılar tarafından
                        ele geçirilmesi ve halkının köle tacirlerine satılmasıyla başlar.</p>
                    <p class="text-muted">Daha sonra Roma Eyaleti'ne dahil edilen ada, birçok savaş ve kuşatma sonucunda
                        M.Ö. 129 yılında Romalılar tarafından ele geçirilmiş. Sonraki dönemlerde ise Bizans dönemi ve
                        Türk kuşatmalarıyla Osmanlı İmparatorluğu'nun egemenliği altına girmiş. Bugün hala adada Bizans
                        dönemine ait eserler mevcut.</p>
                    <div class="row mt-5 align-items-center">
                        <div class="col-12 col-xl-6">
                            <div class="ratio ratio-4x3">
                                <img src="/images/samples/hotel-5.jpg"
                                                              class="w-100 object-fit-cover rounded"
                                                              alt="{{ $guide['title'] }}" loading="lazy"></div>
                        </div>
                        <div class="col-12 col-xl-6"><h2 class="h4">Sedir Adası nasıl bir yer?</h2>
                            <p class="text-muted">Sedir Adası tarihi kalıntılar ve doğal güzelliklerle dolu. Zeytin
                                ağaçları, beyaz kumlu plajlar ve koruma altındaki ormanlarla turistler için çekici bir
                                yer. Özellikle Kleopatra plajı popüler. Ancak, yaz aylarında çok kalabalık olabiliyor.
                                Ada'da çok fazla tesis yok ve yeme içme bakımından tedarik sınırlı. Adaya giderken yeme
                                ve içme için tedarikli olmanda fayda var..</p></div>
                    </div>

                    <!-- BURAYA 1 TANE OTEL GELECEK -->
                    @if($hotel)
                    <section class="mt-5">
                        <h2 class="h5 mb-3">Önerilen Otel</h2>
                        <div class="card h-100 shadow-sm">
                            <div class="card-body p-2">
                                <div class="row align-items-center">
                                    <div class="col-xl-3 mb-3 mb-lg-0">
                                        <a href="{{ route('hotel.detail', ['id' => $hotel->id]) }}">
                                            <img src="{{ $hotel->images[0] ?? '/images/default.jpg' }}" class="img-fluid rounded" alt="otel görseli">
                                        </a>
                                    </div>
                                    <div class="col-xl-5 mb-3 mb-lg-0">
                                        <h4 class="card-title mb-0">{{ $hotel->name }}</h4>

                                        <div class="mb-1 d-flex align-items-center">
                                            @for ($i = 0; $i < ($hotel->stars ?? 0); $i++)
                                            <i class="fi fi-ss-star text-warning"></i>
                                            @endfor
                                            @for ($i = ($hotel->stars ?? 0); $i < 5; $i++)
                                            <i class="fi fi-rs-star text-warning"></i>
                                            @endfor
                                            @if(!empty($hotel->board_type))
                                            <span class="ms-1 text-secondary">{{ $hotel->board_type }}</span>
                                            @endif
                                        </div>

                                        @if(isset($hotel->location->city, $hotel->location->region))

                                        <div class="text-muted small">
                                          {{ $hotel->location->city }}, {{ $hotel->location->region }}
                                        </div>
                                        @endif

                                    </div>

                                    <div class="col-xl-4 text-lg-end">
                                        <div class="d-flex flex-column">
                                            <div class="text-danger small">
                                                <i class="fi fi-rs-user align-middle"></i> yeni üyelere 15% indirim!
                                            </div>
                                            <div>
                                                @if ($firstRoom = ($hotel->rooms[0] ?? null))
                                                <p class="mb-2">
                                                    <strong>{{ number_format($firstRoom->price_per_night) }}₺</strong>'den başlayan fiyatlar
                                                </p>
                                                @endif
                                            </div>
                                            <div class="d-grid mt-1">
                                                <a href="{{ route('hotel.detail', ['id' => $hotel->id]) }}" class="btn btn-outline-primary mt-2">Oteli İncele</a>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </section>
                    @endif

                    <!-- BURAYA 1 TANE VILLA GELECEK GELECEK -->
                    @if($villa)
                    <section class="mt-4">
                        <h2 class="h5 mb-3">Önerilen Villa</h2>
                        <div class="card shadow-sm">
                            <div class="card-body p-2">
                                <div class="row align-items-center">
                                    <!-- Sol: Görsel -->
                                    <div class="col-xl-3 mb-3 mb-lg-0">
                                        <a href="{{ route('villa.villa-detail', ['slug' => $villa['slug']]) }}">
                                            <img src="{{ asset($villa['gallery'][0] ?? '/images/default.jpg') }}"
                                                 class="img-fluid rounded"
                                                 alt="{{ $villa['name']['tr'] }}">
                                        </a>
                                    </div>

                                    <!-- Orta: Bilgiler -->
                                    <div class="col-xl-5 mb-3 mb-lg-0">
                                        <h4 class="card-title mb-0">{{ $villa['name']['tr'] }}</h4>
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

                                        <div class="text-muted small">
                                            {{ $villa['location']['city'] }}, {{ $villa['location']['region'] }}
                                        </div>
                                    </div>
                                    <div class="col-xl-4 text-xl-end">
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
                                                <a href="{{ route('villa.villa-detail', ['slug' => $villa['slug']]) }}"
                                                   class="btn btn-outline-primary mt-auto w-100">
                                                    Villayı İncele
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    @endif


                    <h2
                        class="h4 mt-5 mb-3">Sedir Adası’nda Yapılacak Aktiviteler Neler?</h2>
                    <p class="text-muted">Sedir Adası ve Kleopatra Plajı, özellikle dinlenmek ve kendini doğanın
                        dinginliğine bırakmak isteyen kişiler için ideal bir lokasyon. Doğanın el değmemiş
                        güzelliklerini cömertçe sergilediği bu bölgede yapabileceğiniz en iyi şey uzanmak ve sakinliğin
                        tadını çıkararak güneşlenmek. </p>
                    <p class="text-muted">Kleopatra Plajı’nda bulunan kumlar bölgede sadece Girit’te görülüyor. Özel ve
                        koruma altında olan bu kumların plaj dışına çıkarılması yasak. Plaja gittiğinizde, havlunuzu
                        yere sererek bu muhteşem atmosferi tadabilirsiniz. </p>
                    <p class="text-muted">Sedir Adası’nda adından da anlaşılabileceği gibi pek çok sedir ağacı mevcut.
                        Eğer doğa yürüyüşlerinden hoşlanıyorsanız çevredeki ormanları keşfedebilirsiniz. Ağaçlar
                        arasında yapacağınız keyifli bir yürüyüş ruhunuzu dinlendirmenizi sağlayacak. Özellikle
                        insanların az olduğu yürüyüş rotalarını tercih etmeniz tavsiye ediliyor. </p>
                    <div class="ratio" style="--bs-aspect-ratio: 50%;">
                        <img src="/images/samples/slide-marmaris.jpg" class="w-100 object-fit-cover rounded"
                             alt="{{ $guide['title'] }}" loading="lazy">
                    </div>
                    <h2 class="h4 mt-5 mb-3">Ulaşım</h2>
                    <p class="text-muted">Havalimanı, araç kiralama, toplu taşıma (ileride transfer bannerı
                        gelecek).</p></article>

                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-secondary">Havuz</span>
                    <span class="badge bg-secondary">Denize Yakın</span>
                    <span class="badge bg-secondary">Ücretsiz Wi-Fi</span>
                    <span class="badge bg-secondary">Aile Dostu</span>
                    <span class="badge bg-secondary">Spa</span>
                </div>
            </div>

            <div class="col-xl-4 mt-5 mt-xl-0">
                <!-- Kampanya Banner -->
                <div class="mb-4 position-relative text-white rounded shadow bg-primary rounded"
                     style="min-height: 200px;">
                    <div class="position-absolute bottom-0"
                         style="right:-80px; z-index: 1; overflow: hidden; width: 240px;">
                        <!-- Görsel -->
                        <img src="/images/banner-woman.png" alt="Kampanya Kadın" class="img-fluid"></div>
                    <!-- İçerik -->
                    <div class="position-relative p-4" style="z-index: 2;"><h6 class="fw-light mb-0">Hoş geldin
                            hediyesi!</h6>
                        <h2 class="fw-bold mb-2" style="color: hotpink">%15 indirim</h2>
                        <p class="mb-3 text-shadow-transparent w-75 small">İlk rezevasyonunuzda geçerli <strong
                                class="d-inline-block whitespace-nowrap">%15 indirim</strong> fırsatı!</p>
                        <a href="#"  class="btn btn-outline-light fw-semibold btn-sm">Hesap Oluştur</a>
                    </div>
                </div>

                <!-- Kampanya Banner -->
                <div class="mb-4 position-relative text-white rounded shadow bg-secondary rounded"
                     style="min-height: 160px;">
                    <div class="position-absolute bottom-0"
                         style="right:-15px; z-index: 1; overflow: hidden; width: 220px;"> <!-- Görsel --> <img
                            src="/images/vito.png" alt="Kampanya Kadın" class="img-fluid"></div>
                    <!-- İçerik -->
                    <div class="position-relative p-4" style="z-index: 2;"><h6 class="fw-light mb-0">7 Gece otel
                            rezervasyonunuza</h6> <h4 class="fw-bold mb-2">Ücretsiz Transfer</h4>
                        <a href="#" class="btn btn-outline-light fw-semibold mt-3 btn-sm">Havaalanı
                            Transferi</a></div>
                </div>

                <!-- BURAYA ALT ALTA 2 TANE TUR GELECEK -->
                @if(!empty($excursionsSidebar))
                <h4 class="text-secondary mt-5 mb-3">Bölgenin Popüler Turları</h4>
                @foreach ($excursionsSidebar as $excursion)
                <div class="card shadow-sm position-relative overflow-hidden mb-4">
                    <div class="position-relative">
                        <a href="{{ route('excursions.detail', ['slug' => $excursion['slug']]) }}">
                            <img src="{{ asset($excursion['gallery'][0] ?? '/images/default.jpg') }}" class="card-img-top object-fit-cover" alt="{{ $excursion['name'] }}" height="200">
                        </a>
                        <span class="badge bg-primary position-absolute top-0 end-0 m-2 shadow-sm">{{ $excursion['category'] ?? '' }}</span>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $excursion['name'] }}</h5>
                        <p class="card-text small text-muted">{{ $excursion['short_description'] ?? '' }}</p>
                        <div class="mt-auto mb-3">
                            <div class="text-muted small">Kişi Başı</div>
                            <div class="fw-bold fs-5">{{ $excursion['prices']['adult']['TRY'] ?? '—' }}₺</div>
                        </div>
                        <a href="{{ route('excursions.detail', ['slug' => $excursion['slug']]) }}" class="btn btn-outline-secondary btn-sm">Gezi Detayları ve Rezervasyon</a>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
