{{-- resources/views/partials/cart/item-villa.blade.php --}}

@php
$s = (array) ($ci['snapshot'] ?? []);

$amount   = (float)($ci['amount'] ?? 0);
$currency = $ci['currency'] ?? 'TRY';

// Görsel (villa_image snapshot alanı varsayımı)
$img     = $s['villa_image'] ?? null;
$thumb   = $img['thumb']   ?? null;
$thumb2x = $img['thumb2x'] ?? null;
$alt     = $img['alt']     ?? ($s['villa_name'] ?? 'Villa');

// Tarih / gece
$checkin  = !empty($s['checkin'])  ? \Illuminate\Support\Carbon::parse($s['checkin'])  : null;
$checkout = !empty($s['checkout']) ? \Illuminate\Support\Carbon::parse($s['checkout']) : null;
$nights   = (int)($s['nights'] ?? ($checkin && $checkout ? $checkin->diffInDays($checkout) : 0));

// Kişi sayıları
$adults   = (int)($s['adults']   ?? 0);
$children = (int)($s['children'] ?? 0);

// Fiyatlar
$nightly      = (float)($s['price_nightly']    ?? 0);
$prepayment   = (float)($s['price_prepayment'] ?? $amount);
$total        = (float)($s['price_total']      ?? $amount);
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
        <button type="submit" class="btn btn-sm btn-light text-danger" title="Sil">
            <i class="fi fi-rr-trash"></i>
        </button>
    </form>

    <div class="card-body">
        <div class="row g-3 align-items-center">

            {{-- Görsel --}}
            <div class="col-12 col-lg-3">
                @if ($thumb)
                <img src="{{ $thumb }}"
                     srcset="{{ $thumb }} 1x, {{ $thumb2x }} 2x"
                     class="img-fluid rounded object-fit-cover"
                     alt="{{ $alt }}">
                @endif
            </div>

            {{-- Metinler --}}
            <div class="col-8 col-md-6">
                <div class="small text-uppercase text-muted mb-1">
                    Villa
                </div>

                <h5 class="mb-1">
                    {{ $s['villa_name'] ?? 'Villa' }}
                </h5>

                <div class="text-muted small">
                    @if ($locationLabel)
                    <div class="mb-1">
                        <i class="fi fi-rr-marker"></i>
                        {{ $locationLabel }}
                    </div>
                    @endif

                    @if ($checkin && $checkout)
                    <div>
                        <i class="fi fi-rr-calendar"></i>
                        {{ $checkin->translatedFormat('d M') }}
                        →
                        {{ $checkout->translatedFormat('d M') }}
                        @if ($nights)
                        ({{ $nights }} Gece)
                        @endif
                    </div>
                    @endif

                    @if ($adults || $children)
                    <div>
                        <i class="fi fi-rr-users"></i>
                        {{ $adults }} Yetişkin
                        @if ($children)
                        , {{ $children }} Çocuk
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- Fiyat --}}
            <div class="col-12 col-md-3 text-md-end">
                @if ($prepayment > 0)
                <div class="fw-bold fs-5 text-primary">
                    <small>Ön ödeme:</small><br>
                    {{ number_format($prepayment, 0, ',', '.') }} {{ $currency }}
                </div>
                <div class="small text-muted">
                    Kalan:
                    {{ number_format($remaining, 0, ',', '.') }} {{ $currency }}
                    <i class="fi fi-rr-info"
                       data-bs-toggle="tooltip"
                       data-bs-placement="top"
                       title="Kalan ücret konaklama sırasında alınır."></i>
                </div>
                <div class="small text-muted">
                    Toplam:
                    {{ number_format($total, 0, ',', '.') }} {{ $currency }}
                </div>
                @else
                <div class="fw-bold fs-5 text-primary">
                    {{ number_format($amount, 0, ',', '.') }} {{ $currency }}
                </div>
                @endif
            </div>

        </div>
    </div>
</div>
