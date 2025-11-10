{{-- resources/views/pages/cart.blade.php --}} @extends('layouts.app') @section('title', 'Sepet') @section('content')
<div class="container mt-3" style="font-size: 14px">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#"><i class="fi fi-ss-house-chimney"></i></a></li>
            <li class="breadcrumb-item active" aria-current="page">Page</li>
        </ol>
    </nav>
</div>

<section class="container py-4 py-lg-5">
    <div class="row g-4"> <!-- SOL: Ürünler (8) -->
        <div class="col-lg-8"><h1 class="h4 mb-3">Sepetim</h1>

            {{-- Kuponlarım (Carousel) --}}
            <div class="mb-4 p-4 bg-light rounded" data-coupon-carousel id="couponCarousel1">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0">Kuponlarım</h6>

                    <!-- Oklar -->
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary coupon-prev" aria-label="Önceki" disabled>
                            <i class="fi fi-rr-angle-left"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary coupon-next" aria-label="Sonraki" disabled>
                            <i class="fi fi-rr-angle-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Track -->
                <div class="coupon-viewport overflow-hidden">
                    <div class="coupon-track d-flex gap-3">
                        <!-- Kupon 1 -->
                        <div
                            class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
                            <div class="coupon-badge border me-2 text-center">
                                <div class="h4 fw-bolder text-primary mb-0">%5</div>
                                <div class="badge text-bg-primary">İNDİRİM</div>
                            </div>
                            <div class="flex-grow-1 align-self-end">
                                <div class="small fw-semibold">İlk rezervasyonunuza %5 indirim!</div>
                                <div class="small text-muted">Alt limit: Yok</div>
                                <button class="btn btn-sm btn-outline-primary mt-1 w-100">Uygula</button>
                            </div>
                        </div>

                        <!-- Kupon 2 -->
                        <div
                            class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
                            <div class="coupon-badge border me-2 text-center">
                                <div class="h4 fw-bolder text-primary mb-0">7=6</div>
                                <div class="badge text-bg-primary">GECE</div>
                            </div>
                            <div class="flex-grow-1 align-self-end">
                                <div class="small fw-semibold">7 Gece Kal, 6 Gece Öde!</div>
                                <div class="small text-muted">Alt limit: Yok</div>
                                <button class="btn btn-sm btn-outline-primary mt-1 w-100">Uygula</button>
                            </div>
                        </div>

                        <!-- Kupon 3 -->
                        <div
                            class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
                            <div class="coupon-badge border me-2 text-center">
                                <div class="h4 fw-bolder text-primary mb-0">%10</div>
                                <div class="badge text-bg-primary">İNDİRİM</div>
                            </div>
                            <div class="flex-grow-1 align-self-end">
                                <div class="small fw-semibold">Hafta içi ekstra %10 indirim</div>
                                <div class="small text-muted">Alt limit: 2 Gece</div>
                                <button class="btn btn-sm btn-outline-primary mt-1 w-100">Uygula</button>
                            </div>
                        </div>

                        <!-- Kupon 4 -->
                        <div
                            class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
                            <div class="coupon-badge border me-2 text-center">
                                <div class="h4 fw-bolder text-primary mb-0">%20</div>
                                <div class="badge text-bg-primary">ÖZEL</div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small fw-semibold">Erken rezervasyona özel %20 indirim</div>
                                <div class="small text-muted">Alt limit: 5 Gece</div>
                                <button class="btn btn-sm btn-outline-primary mt-1 w-100">Uygula</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <!-- Öğe: Otel -->
            <div class="card shadow-sm mb-3 position-relative">
                <!-- Sil butonu -->
                <button type="button" class="btn btn-sm btn-light text-danger position-absolute top-0 end-0 m-2"
                        title="Sil">
                    <i class="fi fi-rr-trash"></i>
                </button>

                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-4 col-md-3">
                            <img src="/images/samples/hotel-1.jpg" class="img-fluid rounded object-fit-cover"
                                 alt="Otel görseli">
                        </div>
                        <div class="col-8 col-md-6">
                            <div class="small text-uppercase text-muted mb-1">Otel</div>
                            <h5 class="mb-1">Grand Icmeler Resort</h5>
                            <div class="text-muted small">
                                <div><i class="fi fi-rr-calendar"></i> 20 Aug → 24 Aug (4 Gece)</div>
                                <div><i class="fi fi-rr-users"></i> 2 Yetişkin, 1 Çocuk</div>
                                <div><i class="fi fi-rr-bed"></i> Standart Oda</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 text-md-end">
                            <div class="text-muted text-decoration-line-through small">₺14.000</div>
                            <div class="fw-bold fs-5 text-primary">₺12.450</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Öğe: Transfer -->
            <div class="card shadow-sm mb-3 position-relative">
                <button type="button" class="btn btn-sm btn-light text-danger position-absolute top-0 end-0 m-2"
                        title="Sil">
                    <i class="fi fi-rr-trash"></i>
                </button>

                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-4 col-md-3">
                            <img src="/images/vito.png" class="img-fluid rounded object-fit-cover"
                                 alt="Transfer görseli">
                        </div>
                        <div class="col-8 col-md-6">
                            <div class="small text-uppercase text-muted mb-1">Transfer</div>
                            <h5 class="mb-1">DLM Havalimanı → İçmeler (Vito)</h5>
                            <div class="text-muted small">
                                <div><i class="fi fi-rr-calendar"></i> 20 Aug, 14:30</div>
                                <div><i class="fi fi-rr-users"></i> 2 Yetişkin, 1 Çocuk, 1 Bebek</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 text-md-end">
                            <div class="text-muted text-decoration-line-through small">₺14.000</div>
                            <div class="fw-bold fs-5 text-primary">₺1.800</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Öğe: Günlük Tur -->
            <div class="card shadow-sm mb-3 position-relative">
                <button type="button" class="btn btn-sm btn-light text-danger position-absolute top-0 end-0 m-2"
                        title="Sil">
                    <i class="fi fi-rr-trash"></i>
                </button>

                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-4 col-md-3">
                            <img src="/images/samples/excursion-1b.jpg" class="img-fluid rounded object-fit-cover"
                                 alt="Tur görseli">
                        </div>
                        <div class="col-8 col-md-6">
                            <div class="small text-uppercase text-muted mb-1">Günlük Tur</div>
                            <h5 class="mb-1">Marmaris Tekne Turu</h5>
                            <div class="text-muted small">
                                <div><i class="fi fi-rr-calendar"></i> 22 Aug</div>
                                <div><i class="fi fi-rr-users"></i> 2 Yetişkin, 1 Çocuk</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 text-md-end">
                            <div class="text-muted text-decoration-line-through small">₺2.500</div>
                            <div class="fw-bold fs-5 text-primary">₺2.250</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Öğe: Villa -->
            <div class="card shadow-sm mb-4 position-relative">
                <button type="button" class="btn btn-sm btn-light text-danger position-absolute top-0 end-0 m-2"
                        title="Sil">
                    <i class="fi fi-rr-trash"></i>
                </button>

                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-4 col-md-3">
                            <img src="/images/samples/villa-sample-1.jpg" class="img-fluid rounded object-fit-cover"
                                 alt="Villa görseli">
                        </div>
                        <div class="col-8 col-md-6">
                            <div class="small text-uppercase text-muted mb-1">Villa</div>
                            <h5 class="mb-1">Villa Sedef</h5>
                            <div class="text-muted small">
                                <div><i class="fi fi-rr-calendar"></i> 25 Aug → 30 Aug (5 Gece)</div>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 text-md-end">
                            <div class="fw-bold fs-5 text-primary">Ön Ödeme: ₺3.000</div>
                            <div class="small text-muted">
                                Kalan: ₺15.750
                                <i class="fi fi-rr-info" data-bs-toggle="tooltip" data-bs-placement="top"
                                   title="Kalan ücret konaklama sırasında alınır."></i>
                            </div>
                            <div class="small text-muted">Toplam: ₺18.750</div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Kampanya Banner -->
            <div class="mb-4 position-relative text-white rounded shadow bg-secondary rounded"
                 style="min-height: 160px;">
                <div class="position-absolute bottom-0"
                     style="right:-15px; z-index: 1; overflow: hidden; width: 220px;">
                    <!-- Görsel --> <img
                        src="/images/vito.png" alt="Kampanya Kadın" class="img-fluid"></div>
                <!-- İçerik -->
                <div class="position-relative p-4" style="z-index: 2;"><h6 class="fw-light mb-0">6 Gece otel
                        rezervasyonunuza</h6> <h4 class="fw-bold mb-2">Havalimanı Transferi %10 indirimli!</h4> <a
                        href="/transferler" class="btn btn-outline-light stretched-link fw-semibold mt-3 btn-sm">Hemen
                        Sepetine Ekle</a></div>
            </div>


            <!-- Not Alanı (sepet seviyesi) -->
            <div class="card shadow-sm">
                <div class="card-body"><label class="form-label fw-semibold">Not (opsiyonel)</label> <textarea
                        class="form-control" rows="3" placeholder="Özel istekleriniz..."></textarea></div>
            </div>
        </div> <!-- SAĞ: Özet (4) -->
        <div class="col-lg-4">
            <div class="position-sticky" style="top: 90px;">
                <div class="card shadow-sm mb-3">
                    <div class="card-body"><h2 class="h5 mb-3">Sipariş Özeti</h2>
                        <div class="d-flex justify-content-between small mb-2"><span>Ara toplam</span>
                            <span>₺35.250</span></div>
                        <div class="d-flex justify-content-between small mb-2"><span>Vergiler</span> <span>₺0</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2"><span>İndirimler</span> <span>₺0</span>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center"><span class="fw-semibold">Ödenecek Toplam</span>
                            <span class="fw-bold fs-5">₺35.250</span></div>
                        {{-- Sepetten: ödeme akışına geçiş --}}
                        <a href="{{ route('login', ['redirect' => '/payment', 'from_cart' => 1]) }}"
                           class="btn btn-primary w-100 mt-3">
                            Ödeme Yap
                        </a>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="chkCorporate"
                                   data-bs-toggle="collapse" data-bs-target="#corporateFields"
                                   aria-expanded="false"> <label class="form-check-label"
                                                                 for="chkCorporate"> Kurumsal
                                fatura istiyorum </label></div>
                        <div class="collapse mt-3" id="corporateFields">
                            <div class="mb-2"><label class="form-label small">Firma Adı</label>
                                <input type="text"
                                       class="form-control"
                                       placeholder="Örn. ABC Turizm A.Ş.">
                            </div>
                            <div class="row g-2">
                                <div class="col-6"><label class="form-label small">Vergi Dairesi</label> <input
                                        type="text" class="form-control" placeholder="Örn. Beyoğlu"></div>
                                <div class="col-6"><label class="form-label small">Vergi No</label>
                                    <input type="text"
                                           class="form-control"
                                           placeholder="##########">
                                </div>
                            </div>
                            <div class="mt-2"><label class="form-label small">Fatura Adresi</label>
                                <textarea class="form-control" rows="2" placeholder="Adres"></textarea></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> @endsection
