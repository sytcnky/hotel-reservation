{{-- resources/views/partials/cart/item-hotel.blade.php --}}

@php
$s = (array) ($ci['snapshot'] ?? []);

$amount   = (float)($ci['amount'] ?? 0);
$currency = $ci['currency'] ?? null;

$img     = $s['hotel_image'] ?? null;
$thumb   = $img['thumb']   ?? null;
$thumb2x = $img['thumb2x'] ?? null;
$alt     = $img['alt']     ?? ($s['hotel_name'] ?? 'Otel');

$checkin  = !empty($s['checkin']) ? \Illuminate\Support\Carbon::parse($s['checkin']) : null;
$checkout = !empty($s['checkout']) ? \Illuminate\Support\Carbon::parse($s['checkout']) : null;
$nights   = (int)($s['nights'] ?? ($checkin && $checkout ? $checkin->diffInDays($checkout) : 0));

$adults   = (int)($s['adults']   ?? 0);
$children = (int)($s['children'] ?? 0);

$roomName      = $s['room_name'] ?? null;
$boardTypeName = $s['board_type_name'] ?? null;
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
            <div class="col-4 col-md-3">
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
                    Otel
                </div>

                <h5 class="mb-1">
                    {{ $s['hotel_name'] ?? 'Otel' }}
                </h5>

                <div class="text-muted small">
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

                    <div>
                        {{ $roomName }}, {{ $boardTypeName }}
                    </div>
                </div>
            </div>

            {{-- Fiyat --}}
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
