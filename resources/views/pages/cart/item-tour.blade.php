{{-- resources/views/partials/cart/item-tour.blade.php --}}

@php
    $s = (array) ($ci['snapshot'] ?? []);

    $amount   = (float) ($ci['amount'] ?? 0);
    $currency = $ci['currency'] ?? null;

    $cover = $s['cover_image'] ?? \App\Support\Helpers\ImageHelper::normalize(null);

    if (is_array($cover)) {
        $cover['alt'] = $cover['alt'] ?? ($s['tour_name']);
    }

    $a = (int) ($s['adults']   ?? 0);
    $c = (int) ($s['children'] ?? 0);
    $i = (int) ($s['infants']  ?? 0);
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

            <div class="col-4 col-md-3">
                <x-responsive-image
                    :image="$cover"
                    preset="listing-card"
                    class="img-fluid rounded object-fit-cover"
                    sizes="(min-width: 768px) 160px, 33vw"
                />
            </div>

            <div class="col-8 col-md-6">
                <div class="small text-uppercase text-muted mb-1">
                    @if (!empty($s['category_id']))
                        <small class="badge text-bg-dark fw-normal">
                            {{ \App\Support\Helpers\I18nHelper::scalar(
                                \App\Models\TourCategory::find($s['category_id'])?->name,
                                app()->getLocale(),
                                \App\Support\Helpers\LocaleHelper::defaultCode()
                            ) }}
                        </small>
                    @endif
                </div>

                <h5 class="mb-1">
                    {{ $s['tour_name'] }}
                </h5>

                <div class="text-muted small">
                    @if (!empty($s['date']))
                        <div>
                            {{ \App\Support\Date\DatePresenter::human(
                                ymd: (string) $s['date'],
                                pattern: 'd F'
                            ) }}
                        </div>
                    @endif

                    @if ($a || $c || $i)
                        <div>
                            {{ $a }} {{ t('ui.adult') }}
                            @if ($c) , {{ $c }} {{ t('ui.child') }} @endif
                            @if ($i) , {{ $i }} {{ t('ui.infant') }} @endif
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
