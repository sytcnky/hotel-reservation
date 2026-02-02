{{-- resources/views/partials/cart/item-hotel.blade.php --}}

@php
    $s = (array) ($ci['snapshot'] ?? []);

    $amount   = (float)($ci['amount'] ?? 0);
    $currency = $ci['currency'] ?? null;

    $img = $s['hotel_image'] ?? \App\Support\Helpers\ImageHelper::normalize(null);

    $checkinYmd  = !empty($s['checkin']) ? (string) $s['checkin'] : null;
    $checkoutYmd = !empty($s['checkout']) ? (string) $s['checkout'] : null;

    $nights = (int) ($s['nights'] ?? 0);

    $adults   = (int)($s['adults']   ?? 0);
    $children = (int)($s['children'] ?? 0);

    $roomName      = $s['room_name'] ?? null;
    $boardTypeName = $s['board_type_name'] ?? null;

    $notices = is_array($ci['notices'] ?? null) ? $ci['notices'] : [];
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

            <div class="col-4 col-md-3">
                <x-responsive-image
                    :image="$img"
                    preset="listing-card"
                    class="img-fluid rounded object-fit-cover"
                    sizes="(min-width: 768px) 160px, 33vw"
                />
            </div>

            <div class="col-8 col-md-6">
                <div class="small text-uppercase text-muted mb-1">
                    Otel
                </div>

                <h5 class="mb-1">
                    {{ $s['hotel_name'] ?? 'Otel' }}
                </h5>

                @if (!empty($notices))
                    <div class="mt-2">
                        @foreach ($notices as $n)
                            @php
                                $code   = (string) ($n['code'] ?? '');
                                $params = is_array($n['params'] ?? null) ? $n['params'] : [];
                                $level  = (string) ($n['level'] ?? 'error');
                                $cls    = $level === 'warning' ? 'alert-warning' : 'alert-danger';
                            @endphp
                            @if ($code !== '')
                                <div class="alert {{ $cls }} py-2 px-3 mb-2">
                                    {{ t($code, $params) }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

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

            <div class="col-12 col-md-3 text-md-end">
                <div class="fw-bold fs-5 text-primary">
                    {{ \App\Support\Currency\CurrencyPresenter::format($amount, $currency) }}
                </div>
            </div>

        </div>
    </div>
</div>
