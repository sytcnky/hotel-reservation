@extends('layouts.app', ['pageKey' => 'payment'])

@section('title', t('payment.title_payment'))

@section('content')
    @php
        /** @var \App\Models\CheckoutSession $checkout */
        $checkout = $checkout ?? null;
        abort_if(! $checkout, 500, 'Checkout session missing');

        $endpoint = isset($endpoint) ? (string) $endpoint : '';
        $params   = isset($params) && is_array($params) ? $params : [];

        abort_if($endpoint === '', 500, 'Gateway endpoint missing');
        abort_if(empty($params), 500, 'Gateway params missing');
    @endphp

    <section class="container py-4 py-lg-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <h1 class="h4 mb-3">{{ t('payment.heading_payment') }}</h1>

                <div class="card shadow-sm">
                    <div class="card-body">

                        <div class="alert alert-info mb-0" role="alert">
                            <div class="fw-semibold mb-1">{{ t('payment.redirect_title') }}</div>
                            <div>{{ t('payment.redirect_body') }}</div>
                        </div>

                        <form id="nestpayRedirectForm" method="POST" action="{{ $endpoint }}">
                            @foreach ($params as $k => $v)
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endforeach

                            <noscript>
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        {{ t('payment.redirect_btn_continue') }}
                                    </button>
                                </div>
                            </noscript>
                        </form>

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

    <script>
        (function () {
            const form = document.getElementById('nestpayRedirectForm');
            if (!form) return;
            form.submit();
        })();
    </script>
@endsection
