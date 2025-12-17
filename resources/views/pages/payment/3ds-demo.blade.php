@extends('layouts.app')

@section('title', '3D Secure')

@section('content')
    @php
        /** @var \App\Models\CheckoutSession $checkout */
        $checkout = $checkout ?? null;
        abort_if(!$checkout, 500, 'Checkout session missing');

        $paymentCode = $checkout->code;
        $token = $token ?? null;
        $attemptId = $attemptId ?? null;

        abort_if(empty($token) || empty($attemptId), 500, '3DS params missing');
    @endphp

    <section class="container py-4 py-lg-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">

                <h1 class="h4 mb-3">3D Secure Doğrulama (Demo)</h1>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <p class="text-muted mb-4">
                            Bu ekran, bankanın 3D Secure doğrulama sayfasını simüle eder.
                        </p>

                        <div class="d-grid gap-2">
                            <form method="POST" action="{{ localized_route('payment.3ds.complete', ['code' => $paymentCode]) }}">
                                @csrf
                                <input type="hidden" name="attempt_id" value="{{ (int) $attemptId }}">
                                <input type="hidden" name="token" value="{{ $token }}">
                                <input type="hidden" name="result" value="success">
                                <button type="submit" class="btn btn-success w-100">Doğrulamayı Başarılı Tamamla</button>
                            </form>

                            <form method="POST" action="{{ localized_route('payment.3ds.complete', ['code' => $paymentCode]) }}">
                                @csrf
                                <input type="hidden" name="attempt_id" value="{{ (int) $attemptId }}">
                                <input type="hidden" name="token" value="{{ $token }}">
                                <input type="hidden" name="result" value="fail">
                                <button type="submit" class="btn btn-danger w-100">Doğrulama Başarısız</button>
                            </form>
                        </div>

                        <hr class="my-4">

                        <a class="btn btn-outline-secondary w-100" href="{{ localized_route('cart') }}">
                            Sepete Dön
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
