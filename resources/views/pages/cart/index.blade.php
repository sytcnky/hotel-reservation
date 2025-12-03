{{-- resources/views/pages/cart/index.blade.php --}}
@extends('layouts.app')

@section('content')

    @php
        $txt = [

            // Breadcrumb + titles
            'breadcrumb_cart'  => 'Sepet',
            'title_cart'       => 'Sepetim',

            // OK mesajları
            'ok_validated'      => 'Ürün sepetinize başarıyla eklendi.',
            'ok_coupon_applied' => 'Kupon sepetinize uygulandı.',
            'ok_coupon_removed' => 'Kupon sepetinizden kaldırıldı.',
            'ok_item_removed'   => 'Ürün sepetinizden kaldırıldı.',

            // ERR mesajları
            'err_login_required'   => 'Kupon uygulamak için giriş yapmalısınız.',
            'err_cart_empty'       => 'Sepetiniz boş.',
            'err_no_amount'        => 'Kupon uygulanacak bir tutar bulunamadı.',
            'err_not_applicable'   => 'Bu kupon şu an sepetiniz için kullanılamaz.',
            'err_exclusive_block'  => 'Tek başına kullanılan bir kupon varken başka kupon eklenemez.',
            'currency_mismatch'    => 'Bu kupon seçili para birimiyle kullanılamaz.',
            'min_limit_not_met'    => 'Minimum tutar karşılanmıyor',


            // Coupons
            'coupons_title'         => 'Kuponlarım',
            'coupons_none'          => 'Kullanılabilir kuponunuz bulunmamaktadır.',
            'apply_coupon'          => 'Uygula',
            'coupon_applied'        => 'Uygulandı',

            // Summary
            'summary_title'     => 'Sipariş Özeti',
            'summary_subtotal'  => 'Ara toplam',
            'summary_tax'       => 'Vergiler',
            'summary_discount'  => 'Kupon İndirimleri',
            'summary_total'     => 'Ödenecek Toplam',

            // Buttons
            'btn_pay'       => 'Ödeme Yap',
            'btn_checkout'  => 'Siparişi Tamamla',

            // Corporate Invoice
            'corporate_checkbox' => 'Kurumsal fatura istiyorum',
            'corp_company'       => 'Firma Adı',
            'corp_tax_office'    => 'Vergi Dairesi',
            'corp_tax_no'        => 'Vergi No',
            'corp_address'       => 'Fatura Adresi',

        ];
    @endphp

    <div class="container mt-3" style="font-size: 14px">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ localized_route('home') }}">
                        <i class="fi fi-ss-house-chimney"></i>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $txt['breadcrumb_cart'] }}
                </li>
            </ol>
        </nav>
    </div>

    <section class="container py-4 py-lg-5">
        <div class="row g-4">

            {{-- SOL --}}
            <div class="col-lg-8">

                <h1 class="h4 mb-3">{{ $txt['title_cart'] }}</h1>

                {{-- OK mesajları --}}
                @if (session('ok') === 'validated')
                    <div class="alert alert-success mt-3">{{ $txt['ok_validated'] }}</div>
                @endif

                @if (session('ok') === 'coupon_applied')
                    <div class="alert alert-success mt-3">{{ $txt['ok_coupon_applied'] }}</div>
                @endif

                @if (session('ok') === 'coupon_removed')
                    <div class="alert alert-info mt-3">{{ $txt['ok_coupon_removed'] }}</div>
                @endif

                @if (session('ok') === 'cart_item_removed')
                    <div class="alert alert-info mt-3">{{ $txt['ok_item_removed'] }}</div>
                @endif

                {{-- ERR mesajları --}}
                @if (session('err') === 'err_login_required')
                    <div class="alert alert-danger mt-3">{{ $txt['err_login_required'] }}</div>
                @elseif (session('err') === 'err_cart_empty')
                    <div class="alert alert-danger mt-3">{{ $txt['err_cart_empty'] }}</div>
                @elseif (session('err') === 'err_no_amount')
                    <div class="alert alert-danger mt-3">{{ $txt['err_no_amount'] }}</div>
                @elseif (session('err') === 'err_not_applicable')
                    <div class="alert alert-danger mt-3">{{ $txt['err_not_applicable'] }}</div>
                @elseif (session('err') === 'err_exclusive_block')
                    <div class="alert alert-danger mt-3">{{ $txt['err_exclusive_block'] }}</div>
                @elseif (session('err'))
                    <div class="alert alert-danger mt-3">{{ session('err') }}</div>
                @endif

                {{-- Kuponlar --}}
                <div class="mb-4 p-3 bg-light rounded" id="couponCarousel" data-coupon-carousel>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold mb-0">{{ $txt['coupons_title'] }}</h6>

                        <div class="d-flex align-items-center gap-2">
                            <button class="btn btn-sm btn-outline-secondary coupon-prev" type="button" aria-label="Önceki">
                                <i class="fi fi-rr-angle-left"></i>
                            </button>

                            <button class="btn btn-sm btn-outline-secondary coupon-next" type="button" aria-label="Sonraki">
                                <i class="fi fi-rr-angle-right"></i>
                            </button>
                        </div>
                    </div>

                    @include('partials.coupons.helpers')

                    @php
                        $cartCoupons         = $cartCoupons ?? [];
                        $couponDiscountTotal = $couponDiscountTotal ?? 0;
                        $finalTotal          = $finalTotal ?? $cartSubtotal;
                    @endphp

                    @if (empty($cartCoupons))
                        <div class="small text-muted">{{ $txt['coupons_none'] }}</div>
                    @else
                        <div class="coupon-viewport overflow-hidden">
                            <div class="coupon-track d-flex gap-3">

                                @foreach ($cartCoupons as $coupon)
                                    @php
                                        $texts          = coupon_build_texts($coupon);
                                        $tooltipHtml    = coupon_build_tooltip_html($coupon);
                                        $badgeLabel     = $coupon['badge_label'] ?? '';
                                        $isApplicable   = $coupon['is_applicable'] ?? false;
                                        $disabledReason = $coupon['disabled_reason'] ?? null;
                                        $isApplied      = $coupon['is_applied'] ?? false;
                                    @endphp

                                    <div class="coupon-card border border-2 border-dashed rounded p-2 bg-white d-flex align-items-center">

                                        <div class="coupon-badge border me-2 text-center px-2 py-1"
                                             data-bs-custom-class="coupon-tooltip"
                                             data-bs-toggle="tooltip"
                                             data-bs-placement="top"
                                             data-bs-html="true"
                                             data-bs-custom-class="coupon-tooltip"
                                             title="{!! $tooltipHtml !!}">
                                            <div class="h4 fw-bolder text-primary mb-0">
                                                {{ $coupon['badge_main'] }}
                                            </div>
                                            @if ($badgeLabel)
                                                <div class="badge text-bg-primary mt-1">{{ $badgeLabel }}</div>
                                            @endif
                                            <span class="coupon-info text-secondary">
                                                <i class="fi fi-rr-info"></i>
                                            </span>
                                        </div>

                                        <div class="flex-grow-1 align-self-end">

                                            <div class="small fw-semibold d-flex align-items-center">
                                                <span>{{ $coupon['title'] ?: $coupon['code'] }}</span>
                                            </div>

                                            @php
                                                // Alt limit satırı için renk seçimi
                                                $altLimitClass =
                                                    ($coupon['min_booking_amount'] ?? null)
                                                        ? ($isApplicable ? 'text-success' : 'text-danger')
                                                        : 'text-muted';
                                            @endphp

                                            <div class="small {{ $altLimitClass }}">
                                                {{ $texts['alt_limit'] }}
                                            </div>

                                            @if ($isApplicable)

                                                @if ($isApplied)
                                                    <form method="POST"
                                                          action="{{ route('cart.coupon.remove') }}"
                                                          class="mt-1">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="user_coupon_id" value="{{ $coupon['id'] }}">
                                                        <button type="submit"
                                                                class="btn btn-sm btn-success w-100">
                                                            {{ $txt['coupon_applied'] }}
                                                        </button>
                                                    </form>

                                                @else
                                                    <form method="POST"
                                                          action="{{ route('cart.coupon.apply') }}"
                                                          class="mt-1">
                                                        @csrf
                                                        <input type="hidden" name="user_coupon_id" value="{{ $coupon['id'] }}">
                                                        <button type="submit"
                                                                class="btn btn-sm btn-outline-primary w-100">
                                                            {{ $txt['apply_coupon'] }}
                                                        </button>
                                                    </form>
                                                @endif

                                            @else
                                                {{-- Uygulanamaz → Artık sadece disabled buton --}}
                                                <button class="btn btn-sm btn-outline-secondary mt-1 w-100" disabled>
                                                    {{ $txt['apply_coupon'] }}
                                                </button>
                                            @endif


                                        </div>
                                    </div>

                                @endforeach

                            </div>
                        </div>
                    @endif
                </div>


                {{-- DİNAMİK ÜRÜNLER --}}
                @if (!empty($cartItems))
                    @foreach ($cartItems as $key => $ci)
                        @php $type = $ci['product_type'] ?? 'unknown'; @endphp

                        @if ($type === 'transfer')
                            @include('pages.cart.item-transfer', ['key'=>$key,'ci'=>$ci])
                        @elseif ($type === 'tour' || $type === 'excursion')
                            @include('pages.cart.item-tour', ['key'=>$key,'ci'=>$ci])
                        @elseif ($type === 'hotel' || $type === 'hotel_room')
                            @include('pages.cart.item-hotel', ['key'=>$key,'ci'=>$ci])
                        @elseif ($type === 'villa')
                            @include('pages.cart.item-villa', ['key'=>$key,'ci'=>$ci])
                        @else
                            @php
                                $amount   = (float)($ci['amount'] ?? 0);
                                $currency = $ci['currency'] ?? $cartCurrency;
                            @endphp

                            <div class="card shadow-sm mb-3 position-relative">

                                <form method="POST"
                                      action="{{ route('cart.remove', ['key'=>$key]) }}"
                                      class="position-absolute top-0 end-0 m-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-sm btn-light text-danger">
                                        <i class="fi fi-rr-trash"></i>
                                    </button>
                                </form>

                                <div class="card-body">
                                    <div class="row g-3 align-items-center">

                                        <div class="col-4 col-md-3">
                                            <img src="{{ asset('/images/samples/excursion-1b.jpg') }}"
                                                 class="img-fluid rounded object-fit-cover"
                                                 alt="urun">
                                        </div>

                                        <div class="col-8 col-md-6">
                                            <div class="small text-uppercase text-muted mb-1">
                                                {{ ucfirst($type) }}
                                            </div>
                                            <h5 class="mb-1">
                                                {{ data_get($ci,'snapshot.tour_name','Ürün') }}
                                            </h5>
                                        </div>

                                        <div class="col-12 col-md-3 text-md-end">
                                            <div class="fw-bold fs-5 text-primary">
                                                {{ number_format($amount,0,',','.') }}
                                                @if ($currency) {{ $currency }} @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        @endif

                    @endforeach
                @endif


                {{-- Banner --}}
                <div class="mb-4 position-relative text-white rounded shadow bg-secondary" style="min-height:160px;">
                    <div class="position-absolute bottom-0" style="right:-15px;z-index:1;overflow:hidden;width:220px;">
                        <img src="{{ asset('/images/vito.png') }}" class="img-fluid">
                    </div>
                    <div class="position-relative p-4" style="z-index:2;">
                        <h6 class="fw-light mb-0">6 Gece otel rezervasyonunuza</h6>
                        <h4 class="fw-bold mb-2">Havalimanı Transferi %10 indirimli!</h4>
                        <a href="{{ localized_route('transfers') }}"
                           class="btn btn-outline-light stretched-link fw-semibold mt-3 btn-sm">
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

            {{-- SAĞ: ÖZET --}}
            <div class="col-lg-4">

                <div class="position-sticky" style="top:90px;">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">

                            <h2 class="h5 mb-3">{{ $txt['summary_title'] }}</h2>

                            <div class="d-flex justify-content-between small mb-2">
                                <span>{{ $txt['summary_subtotal'] }}</span>
                                <span>
                                    {{ number_format($cartSubtotal,0,',','.') }}
                                    @if($cartCurrency) {{ $cartCurrency }} @endif
                                </span>
                            </div>

                            <div class="d-flex justify-content-between small mb-2">
                                <span>{{ $txt['summary_tax'] }}</span>
                                <span>
                                    0 @if($cartCurrency) {{ $cartCurrency }} @endif
                                </span>
                            </div>

                            @if ($couponDiscountTotal > 0)
                                <div class="d-flex justify-content-between small mb-2">
                                    <span>{{ $txt['summary_discount'] }}</span>
                                    <span>
                                        -{{ number_format($couponDiscountTotal,0,',','.') }}
                                        @if($cartCurrency) {{ $cartCurrency }} @endif
                                    </span>
                                </div>
                            @endif

                            <hr class="my-3">

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-semibold">{{ $txt['summary_total'] }}</span>
                                <span class="fw-bold fs-5">
                                    {{ number_format($finalTotal,0,',','.') }}
                                    @if($cartCurrency) {{ $cartCurrency }} @endif
                                </span>
                            </div>

                            <a href="{{ route('login',['redirect'=>'/payment','from_cart'=>1]) }}"
                               class="btn btn-primary w-100 mt-3">
                                {{ $txt['btn_pay'] }}
                            </a>

                            <form method="POST"
                                  action="{{ localized_route('checkout.complete') }}"
                                  class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    {{ $txt['btn_checkout'] }}
                                </button>
                            </form>

                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="chkCorporate"
                                       data-bs-toggle="collapse" data-bs-target="#corporateFields">
                                <label class="form-check-label" for="chkCorporate">
                                    {{ $txt['corporate_checkbox'] }}
                                </label>
                            </div>

                            <div class="collapse mt-3" id="corporateFields">

                                <div class="mb-2">
                                    <label class="form-label small">{{ $txt['corp_company'] }}</label>
                                    <input type="text" class="form-control" placeholder="Örn. ABC Turizm A.Ş.">
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small">{{ $txt['corp_tax_office'] }}</label>
                                        <input type="text" class="form-control" placeholder="Örn. Beyoğlu">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small">{{ $txt['corp_tax_no'] }}</label>
                                        <input type="text" class="form-control" placeholder="##########">
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label class="form-label small">{{ $txt['corp_address'] }}</label>
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
