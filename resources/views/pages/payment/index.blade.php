@extends('layouts.app', ['pageKey' => 'payment'])

@section('title', t('payment.title_payment'))

@section('content')
    @php
        /** @var \App\Models\CheckoutSession $checkout */
        $checkout = $checkout ?? null;

        abort_if(! $checkout, 500, 'Checkout session missing');

        $totalAmount = isset($totalAmount)
            ? (float) $totalAmount
            : max((float) $checkout->cart_total - (float) $checkout->discount_amount, 0);

        $currency = $currency ?? ($checkout->currency ?? null);
    @endphp

    <section class="container py-4 py-lg-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <h1 class="h4 mb-3">{{ t('payment.heading_payment') }}</h1>

                <div class="card shadow-sm">
                    <div class="card-body">

                        <h3 class="text-muted mb-3">
                            {{ t('payment.label_payable_amount') }}
                            <span class="fw-semibold text-body">
                                {{ \App\Support\Currency\CurrencyPresenter::format($totalAmount, $currency) }}
                            </span>
                        </h3>

                        <div class="alert alert-info mb-0" role="alert">
                            <div class="fw-semibold mb-1">{{ t('payment.placeholder_title') }}</div>
                            <div>{{ t('payment.placeholder_body') }}</div>
                        </div>

                    </div>
                </div>

                <div class="mt-3">
                    <a class="btn btn-outline-secondary" href="{{ localized_route('cart') }}">
                        {{ t('payment.btn_back_to_cart') }}
                    </a>
                </div>

            </div>
        </div>
    </section>
@endsection
