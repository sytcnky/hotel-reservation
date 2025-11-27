{{-- resources/views/pages/cart/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mt-3" style="font-size: 14px">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ localized_route('home') }}">
                    <i class="fi fi-ss-house-chimney"></i>
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">Sepet</li>
        </ol>
    </nav>
</div>

<section class="container py-4 py-lg-5">
    <div class="row g-4">
        {{-- SOL: Ürünler --}}
        <div class="col-lg-8">
            <h1 class="h4 mb-3">Sepetim</h1>

            @if (session('ok') === 'validated')
            <div class="alert alert-success mt-3">Ürün sepetinize başarıyla eklendi.</div>
            @endif

            {{-- Kuponlarım (dummy / görsel amaçlı) --}}
            <div class="mb-4 p-4 bg-light rounded" id="couponCarousel1">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold mb-0">Kuponlarım</h6>
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" aria-label="Önceki" disabled>
                            <i class="fi fi-rr-angle-left"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" aria-label="Sonraki" disabled>
                            <i class="fi fi-rr-angle-right"></i>
                        </button>
                    </div>
                </div>

                <div class="coupon-viewport overflow-hidden">
                    <div class="coupon-track d-flex gap-3">
                        {{-- ... dummy kupon kartları ... --}}
                        {{-- (Burayı değiştirmiyorum) --}}
                        <div class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
                            <div class="coupon-badge border me-2 text-center">
                                <div class="h4 fw-bolder text-primary mb-0">%5</div>
                                <div class="badge text-bg-primary">İNDİRİM</div>
                            </div>
                            <div class="flex-grow-1 align-self-end">
                                <div class="small fw-semibold">İlk rezervasyonunuza %5 indirim!</div>
                                <div class="small text-muted">Alt limit: Yok</div>
                                <button class="btn btn-sm btn-outline-primary mt-1 w-100" type="button">Uygula</button>
                            </div>
                        </div>

                        <div class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
                            <div class="coupon-badge border me-2 text-center">
                                <div class="h4 fw-bolder text-primary mb-0">7=6</div>
                                <div class="badge text-bg-primary">GECE</div>
                            </div>
                            <div class="flex-grow-1 align-self-end">
                                <div class="small fw-semibold">7 Gece Kal, 6 Gece Öde!</div>
                                <div class="small text-muted">Alt limit: Yok</div>
                                <button class="btn btn-sm btn-outline-primary mt-1 w-100" type="button">Uygula</button>
                            </div>
                        </div>

                        <div class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
                            <div class="coupon-badge border me-2 text-center">
                                <div class="h4 fw-bolder text-primary mb-0">%10</div>
                                <div class="badge text-bg-primary">İNDİRİM</div>
                            </div>
                            <div class="flex-grow-1 align-self-end">
                                <div class="small fw-semibold">Hafta içi ekstra %10 indirim</div>
                                <div class="small text-muted">Alt limit: 2 Gece</div>
                                <button class="btn btn-sm btn-outline-primary mt-1 w-100" type="button">Uygula</button>
                            </div>
                        </div>

                        <div class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">
                            <div class="coupon-badge border me-2 text-center">
                                <div class="h4 fw-bolder text-primary mb-0">%20</div>
                                <div class="badge text-bg-primary">ÖZEL</div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small fw-semibold">Erken rezervasyona özel %20 indirim</div>
                                <div class="small text-muted">Alt limit: 5 Gece</div>
                                <button class="btn btn-sm btn-outline-primary mt-1 w-100" type="button">Uygula</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- >>> DİNAMİK: Session’daki sepet öğeleri (yeni eklenenler en üstte) --}}
            @php
            $cartItems    = session('cart.items', []);
            $cartSubtotal = 0;
            $cartCurrency = null;

            foreach ($cartItems as $ci) {
            $amount = (float)($ci['amount'] ?? 0);
            $cartSubtotal += $amount;

            if ($cartCurrency === null && !empty($ci['currency'])) {
            $cartCurrency = $ci['currency'];
            }
            }
            @endphp

            @if (!empty($cartItems))
            @foreach ($cartItems as $key => $ci)
            @php
            $type = $ci['product_type'] ?? 'unknown';
            @endphp

            @if ($type === 'transfer')
            @include('pages.cart.item-transfer', [
            'key' => $key,
            'ci'  => $ci,
            ])

            @elseif ($type === 'tour' || $type === 'excursion')
            @include('pages.cart.item-tour', [
            'key' => $key,
            'ci'  => $ci,
            ])

            @elseif ($type === 'hotel' || $type === 'hotel_room')
            @include('pages.cart.item-hotel', [
            'key' => $key,
            'ci'  => $ci,
            ])

            @elseif ($type === 'villa')
            @include('pages.cart.item-villa', [
            'key' => $key,
            'ci'  => $ci,
            ])

            @else
            {{-- DİĞER / GEÇİCİ ÜRÜNLER --}}
            @php
            $amount   = (float)($ci['amount'] ?? 0);
            $currency = $ci['currency'] ?? $cartCurrency;
            @endphp

            <div class="card shadow-sm mb-3 position-relative">
                <form method="POST"
                      action="{{ route('cart.remove', ['key' => $key]) }}"
                      class="position-absolute top-0 end-0 m-2">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="btn btn-sm btn-light text-danger"
                            title="Sil">
                        <i class="fi fi-rr-trash"></i>
                    </button>
                </form>

                <div class="card-body">
                    <div class="row g-3 align-items-center">
                        <div class="col-4 col-md-3">
                            <img src="{{ asset('/images/samples/excursion-1b.jpg') }}"
                                 class="img-fluid rounded object-fit-cover"
                                 alt="Sepet ürünü">
                        </div>
                        <div class="col-8 col-md-6">
                            <div class="small text-uppercase text-muted mb-1">
                                {{ ucfirst($type) }}
                            </div>
                            <h5 class="mb-1">
                                {{ data_get($ci, 'snapshot.tour_name', 'Ürün') }}
                            </h5>
                        </div>
                        <div class="col-12 col-md-3 text-md-end">
                            <div class="fw-bold fs-5 text-primary">
                                {{ number_format($amount, 0, ',', '.') }}
                                @if ($currency)
                                {{ $currency }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
            @endif
            {{-- <<< DİNAMİK BİTİŞ --}}

            {{-- Kampanya Banner (dummy) --}}
            <div class="mb-4 position-relative text-white rounded shadow bg-secondary" style="min-height: 160px;">
                <div class="position-absolute bottom-0" style="right:-15px; z-index: 1; overflow: hidden; width: 220px;">
                    <img src="{{ asset('/images/vito.png') }}" alt="Kampanya" class="img-fluid">
                </div>
                <div class="position-relative p-4" style="z-index: 2;">
                    <h6 class="fw-light mb-0">6 Gece otel rezervasyonunuza</h6>
                    <h4 class="fw-bold mb-2">Havalimanı Transferi %10 indirimli!</h4>
                    <a href="{{ localized_route('transfers') }}" class="btn btn-outline-light stretched-link fw-semibold mt-3 btn-sm">
                        Hemen Rezervasyon Yap
                    </a>
                </div>
            </div>

            {{-- Not Alanı --}}
            <div class="card shadow-sm">
                <div class="card-body">
                    <label class="form-label fw-semibold">Not (opsiyonel)</label>
                    <textarea class="form-control" rows="3" placeholder="Özel istekleriniz..."></textarea>
                </div>
            </div>
        </div>

        {{-- SAĞ: Özet --}}
        <div class="col-lg-4">
            <div class="position-sticky" style="top: 90px;">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Sipariş Özeti</h2>
                        <div class="d-flex justify-content-between small mb-2">
                            <span>Ara toplam</span>
                            <span>
                                {{ number_format($cartSubtotal, 0, ',', '.') }}
                                @if ($cartCurrency)
                                    {{ $cartCurrency }}
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span>Vergiler</span>
                            <span>
                                {{ number_format(0, 0, ',', '.') }}
                                @if ($cartCurrency)
                                    {{ $cartCurrency }}
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span>İndirimler</span>
                            <span>
                                {{ number_format(0, 0, ',', '.') }}
                                @if ($cartCurrency)
                                    {{ $cartCurrency }}
                                @endif
                            </span>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Ödenecek Toplam</span>
                            <span class="fw-bold fs-5">
                                {{ number_format($cartSubtotal, 0, ',', '.') }}
                                @if ($cartCurrency)
                                    {{ $cartCurrency }}
                                @endif
                            </span>
                        </div>

                        <a href="{{ route('login', ['redirect' => '/payment', 'from_cart' => 1]) }}"
                           class="btn btn-primary w-100 mt-3">
                            Ödeme Yap
                        </a>

                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" id="chkCorporate" data-bs-toggle="collapse" data-bs-target="#corporateFields" aria-expanded="false">
                            <label class="form-check-label" for="chkCorporate">Kurumsal fatura istiyorum</label>
                        </div>

                        <div class="collapse mt-3" id="corporateFields">
                            <div class="mb-2">
                                <label class="form-label small">Firma Adı</label>
                                <input type="text" class="form-control" placeholder="Örn. ABC Turizm A.Ş.">
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small">Vergi Dairesi</label>
                                    <input type="text" class="form-control" placeholder="Örn. Beyoğlu">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small">Vergi No</label>
                                    <input type="text" class="form-control" placeholder="##########">
                                </div>
                            </div>
                            <div class="mt-2">
                                <label class="form-label small">Fatura Adresi</label>
                                <textarea class="form-control" rows="2" placeholder="Adres"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
