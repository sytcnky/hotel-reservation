{{-- resources/views/partials/cart/item-hotel.blade.php --}}

@php
    $s = (array) ($ci['snapshot'] ?? []);

    $amount   = (float)($ci['amount'] ?? 0);
    $currency = $ci['currency'] ?? null;

    $img = $s['hotel_image'] ?? \App\Support\Helpers\ImageHelper::normalize(null);

    // Tarih / gece
    $checkinYmd  = !empty($s['checkin']) ? (string) $s['checkin'] : null;
    $checkoutYmd = !empty($s['checkout']) ? (string) $s['checkout'] : null;

    // Nights snapshot'ta geliyorsa onu kullan; yoksa hesaplamayı burada yapma (strict kalsın).
    $nights = (int) ($s['nights'] ?? 0);

    // Kişi sayıları
    $adults   = (int)($s['adults']   ?? 0);
    $children = (int)($s['children'] ?? 0);

    // Fiyatlar
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
                <x-responsive-image
                    :image="$img"
                    preset="listing-card"
                    class="img-fluid rounded object-fit-cover"
                    sizes="(min-width: 768px) 160px, 33vw"
                />
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
                    <div>
                        {{ $roomName }}, {{ $boardTypeName }}
                    </div>
                    @if ($checkinYmd && $checkoutYmd)
                        <div>
                            <i class="fi fi-rr-calendar"></i>
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
                <div class="fw-bold fs-5 text-primary">
                    {{ \App\Support\Currency\CurrencyPresenter::format($amount, $currency) }}
                </div>
            </div>

        </div>
    </div>
</div>
