{{-- resources/views/partials/cart/item-villa.blade.php --}}

@php
    $s = (array) ($ci['snapshot'] ?? []);

    $amount   = (float)($ci['amount'] ?? 0);
    $currency = $ci['currency'] ?? null;

    $img = $s['villa_image'] ?? \App\Support\Helpers\ImageHelper::normalize(null);

    // Tarih / gece
    $checkinYmd  = !empty($s['checkin'])  ? (string) $s['checkin']  : null;
    $checkoutYmd = !empty($s['checkout']) ? (string) $s['checkout'] : null;
    $nights      = (int) ($s['nights'] ?? 0);

    // Kişi sayıları
    $adults   = (int)($s['adults']   ?? 0);
    $children = (int)($s['children'] ?? 0);

    // Fiyatlar
    $nightly      = (float)($s['price_nightly']    ?? 0);
    $prepayment   = (float)($s['price_prepayment'] ?? $amount);
    $total        = (float)($s['price_total']      ?? $amount);

    // Villa kontratı: prepayment = sistemde tahsil edilen nihai tutar (total kavramı eşitlenmiş durumda).
    // Kalan ödeme ev sahibine elden → UI'da kalan gösterimi opsiyonel ama tutarlı kalsın.
    $remaining    = max($total - $prepayment, 0);

    // Lokasyon etiketi (opsiyonel)
    $locationLabel = $s['location_label'] ?? null;
@endphp

<div class="card shadow-sm mb-3 position-relative">
    <form method="POST"
          action="{{ route('cart.remove', ['key' => $key]) }}"
          class="position-absolute top-0 end-0 m-2">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-light text-danger" title="{{ t('cart.item_delete') }}">
            <i class="fi fi-rr-trash"></i>
        </button>
    </form>

    <div class="card-body">
        <div class="row g-3 align-items-center">

            {{-- Görsel --}}
            <div class="col-12 col-lg-3">
                <x-responsive-image
                    :image="$img"
                    preset="listing-card"
                    class="img-fluid rounded object-fit-cover"
                    sizes="(min-width: 768px) 160px, 33vw"
                />
            </div>

            {{-- Metinler --}}
            <div class="col-8 col-md-6">
                <h5 class="mb-1">
                    {{ $s['villa_name'] ?? 'Villa' }}
                </h5>

                <div class="text-muted small">
                    @if ($locationLabel)
                        <div class="mb-1">
                            {{ $locationLabel }}
                        </div>
                    @endif

                    @if ($checkinYmd && $checkoutYmd)
                        <div>
                            {{ \App\Support\Date\DatePresenter::human(
                                ymd: (string) $checkinYmd,
                                pattern: 'd F'
                            ) }}
                            →
                            {{ \App\Support\Date\DatePresenter::human(
                                ymd: (string) $checkoutYmd,
                                pattern: 'd F'
                            ) }}
                            @if ($nights)
                                ({{ $nights }} {{ t('ui.night') }})
                            @endif
                        </div>
                    @endif

                    @if ($adults || $children)
                        <div>
                            {{ $adults }} {{ t('ui.adult') }}
                            @if ($children)
                                , {{ $children }} {{ t('ui.child') }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Fiyat --}}
            <div class="col-12 col-md-3 text-md-end">
                @if ($prepayment > 0)
                    <div class="fw-bold fs-5 text-primary">
                        {{ \App\Support\Currency\CurrencyPresenter::format($prepayment, $currency) }}
                    </div>
                    <div class="small text-muted">
                        {{ t('ui.villa.remaining') }}: {{ \App\Support\Currency\CurrencyPresenter::format($remaining, $currency) }}
                        <i class="fi fi-rr-info"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           title="{{ t('ui.villa.prepayment') }}"></i>
                    </div>
                    <div class="small text-muted">
                        {{ t('ui.villa.total') }}: {{ \App\Support\Currency\CurrencyPresenter::format($total, $currency) }}
                    </div>
                @else
                    <div class="fw-bold fs-5 text-primary">
                        {{ \App\Support\Currency\CurrencyPresenter::format($amount, $currency) }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
