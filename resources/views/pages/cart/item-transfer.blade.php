{{-- resources/views/pages/cart/partials/item-transfer.blade.php --}}
@php
    $s = (array) ($ci['snapshot'] ?? []);

    $vehicleImage = $s['vehicle_cover'] ?? \App\Support\Helpers\ImageHelper::normalize(null);

    $adults   = (int)($s['adults']   ?? 0);
    $children = (int)($s['children'] ?? 0);
    $infants  = (int)($s['infants']  ?? 0);

    $direction = $s['direction'] ?? null;

    $amount   = (float)($ci['amount'] ?? 0);
    $currency = $ci['currency'] ?? null;
@endphp

<div class="card shadow-sm mb-3 position-relative">
    {{-- Sil butonu --}}
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
                <x-responsive-image
                    :image="$vehicleImage"
                    preset="listing-card"
                    class="img-fluid rounded object-fit-cover"
                    sizes="(min-width: 768px) 160px, 33vw"
                />
            </div>

            <div class="col-8 col-md-6">
                <div class="small text-uppercase text-muted mb-1">
                    Transfer
                    <small>
                        {{ $direction === 'roundtrip' ? '(Gidiş-Dönüş)' : '(Tek Yön)' }}
                    </small>
                </div>

                <h5 class="mb-1">
                    {{ $s['from_label'] ?? $s['from_location_id'] ?? '' }}
                    →
                    {{ $s['to_label'] ?? $s['to_location_id'] ?? '' }}
                </h5>

                <div class="text-muted small">
                    @if (!empty($s['departure_date']))
                        <div>
                            <i class="fi fi-rr-calendar"></i>
                            {{ $s['departure_date'] }}
                            @if (!empty($s['pickup_time_outbound'])),
                            {{ $s['pickup_time_outbound'] }}
                            @endif
                        </div>
                    @endif

                    @if ($direction === 'roundtrip' && !empty($s['return_date']))
                        <div>
                            <i class="fi fi-rr-calendar"></i>
                            {{ $s['return_date'] }}
                            @if (!empty($s['pickup_time_return'])),
                            {{ $s['pickup_time_return'] }}
                            @endif
                        </div>
                    @endif

                    <div>
                        <i class="fi fi-rr-users"></i>
                        {{ $adults }} Yetişkin
                        @if ($children) , {{ $children }} Çocuk @endif
                        @if ($infants) , {{ $infants }} Bebek @endif
                    </div>
                </div>
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
