@extends('layouts.app', ['pageKey' => 'payment'])
@section('title', 'Ödeme')
@section('content')
    @php
        /** @var \App\Models\CheckoutSession $checkout */
        $checkout = $checkout ?? null;

        abort_if(!$checkout, 500, 'Checkout session missing');

        $paymentCode  = $checkout->code;
        $totalAmount  = isset($totalAmount) ? (float) $totalAmount : max((float) $checkout->cart_total - (float) $checkout->discount_amount, 0);
        $currency = $currency ?? ($checkout->currency ?? null);
        $submitNonce  = $submitNonce ?? null;
        $attemptError = $attemptError ?? null;
    @endphp

    <section class="container py-4 py-lg-5">
        <div class="row g-4">

            <!-- SOL: Ödeme Formu -->
            <div class="col-lg-7">
                <h1 class="h4 mb-3">Ödeme Bilgileri</h1>

                <h3 class="text-muted mb-3">
                    Ödenecek Tutar:
                    <span class="fw-semibold text-body">
                        {{ \App\Support\Currency\CurrencyPresenter::format($totalAmount, $currency) }}
                    </span>
                </h3>

                <div class="card shadow-sm">
                    <div class="card-body">
                        {{-- Sprint 4: ikinci mesaj kanalı yok.
                             Mesajlar yalnızca resources/views/partials/alerts.blade.php üzerinden akar:
                             - session('notices')
                             - $errors->first('global')
                        --}}

                        <form method="post"
                              id="paymentForm"
                              action="{{ $checkout->type === \App\Models\CheckoutSession::TYPE_GUEST
                                  ? URL::signedRoute(app()->getLocale() . '.payment.process', ['code' => $checkout->code])
                                  : localized_route('payment.process', ['code' => $checkout->code])
                              }}"
                              autocomplete="on"
                              novalidate>
                            @csrf

                            @if(!empty($submitNonce))
                                <input type="hidden" name="submit_nonce" value="{{ $submitNonce }}">
                            @endif

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Kart Üzerindeki İsim</label>
                                    <input type="text"
                                           class="form-control"
                                           id="ccHolder"
                                           name="cardholder"
                                           placeholder="Ad Soyad"
                                           maxlength="30"
                                           autocomplete="cc-name"
                                           required>
                                    <div class="invalid-feedback">
                                        Lütfen kart üzerindeki ismi girin (en az 3 karakter).
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Kart Numarası</label>

                                    <div class="input-group has-validation">
                                        <span class="input-group-text">
                                            <i class="fi fi-rr-credit-card"></i>
                                        </span>

                                        <input type="text"
                                               class="form-control"
                                               id="ccNumber"
                                               name="cardnumber"
                                               inputmode="numeric"
                                               autocomplete="cc-number"
                                               placeholder="•••• •••• •••• ••••"
                                               required>

                                        <div class="invalid-feedback">
                                            Lütfen geçerli bir kart numarası girin.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label">Son Kullanma</label>

                                    <div class="input-group has-validation">
                                        <input type="text"
                                               class="form-control"
                                               id="ccExpiryMonth"
                                               name="exp-month"
                                               inputmode="numeric"
                                               autocomplete="cc-exp-month"
                                               placeholder="AA"
                                               maxlength="2"
                                               required>

                                        <span class="input-group-text">/</span>

                                        <input type="text"
                                               class="form-control"
                                               id="ccExpiryYear"
                                               name="exp-year"
                                               inputmode="numeric"
                                               autocomplete="cc-exp-year"
                                               placeholder="YY"
                                               maxlength="2"
                                               required>

                                        <div class="invalid-feedback">
                                            Lütfen geçerli bir son kullanma tarihi girin (AA / YY).
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 col-md-4">
                                    <label class="form-label d-flex justify-content-between align-items-center">
                                        <span>CVV</span>
                                        <i class="fi fi-rr-info"
                                           data-bs-toggle="tooltip"
                                           title="Kartınızın arka yüzündeki 3-4 haneli güvenlik kodu."></i>
                                    </label>

                                    <div class="input-group has-validation">
                                        <span class="input-group-text">
                                            <i class="fi fi-rr-lock"></i>
                                        </span>

                                        <input type="text"
                                               class="form-control"
                                               id="ccCvv"
                                               name="cvc"
                                               inputmode="numeric"
                                               autocomplete="cc-csc"
                                               placeholder="•••"
                                               maxlength="4"
                                               required>

                                        <div class="invalid-feedback">
                                            Lütfen kartınızın 3 veya 4 haneli güvenlik kodunu girin.
                                        </div>
                                    </div>
                                </div>

                                @if(config('icr.payments.driver') === 'demo')
                                    <div class="col-12">
                                        <label class="form-label">Demo Ödeme Sonucu</label>
                                        <select class="form-select" name="demo_outcome">
                                            <option value="success">Başarılı ödeme</option>
                                            <option value="fail">Başarısız ödeme</option>
                                        </select>
                                        <div class="form-text">
                                            Bu alan sadece demo ödeme modunda görünür.
                                        </div>
                                    </div>
                                @endif

                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="terms"
                                               name="terms"
                                               required>
                                        <label class="form-check-label" for="terms">
                                            Ön bilgilendirme ve mesafeli satış sözleşmesini okudum, kabul ediyorum.
                                        </label>

                                        <div class="invalid-feedback">
                                            Devam etmek için sözleşmeyi onaylamalısınız.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <button class="btn btn-primary w-100"
                                            type="submit"
                                            id="btnSubmitPayment">
                                        <span class="btn-label">Ödemeyi Tamamla</span>
                                        <span class="btn-loading d-none" aria-hidden="true">
                                            <span class="spinner-border spinner-border-sm align-middle me-2" role="status" aria-hidden="true"></span>
                                            Lütfen bekleyiniz...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

            <!-- SAĞ: Kart Önizleme -->
            <div class="col-lg-5">
                <div class="mb-4">
                    <div class="cc">
                        <div class="cc-inner" id="ccInner">
                            <div class="cc-face cc-front">
                                <div class="cc-top">
                                    <div class="cc-chip"></div>
                                    <div class="cc-brand">
                                        <img id="ccBrandLogo" class="cc-brand-logo" alt="">
                                    </div>
                                </div>
                                <div class="cc-number" id="ccNumberVis">•••• •••• •••• ••••</div>
                                <div class="cc-bottom">
                                    <div class="cc-holder">
                                        <div class="cc-label">KART SAHİBİ</div>
                                        <div class="cc-value" id="ccHolderVis">AD SOYAD</div>
                                    </div>
                                    <div class="cc-exp">
                                        <div class="cc-label">SKT</div>
                                        <div class="cc-value" id="ccExpiryVis">MM/YY</div>
                                    </div>
                                </div>
                            </div>

                            <div class="cc-face cc-back">
                                <div class="cc-strip"></div>
                                <div class="cc-cvv-box">
                                    <div class="cc-label">CVV</div>
                                    <div class="cc-cvv" id="ccCvvVis">•••</div>
                                </div>
                                <div class="cc-brand cc-brand-back">
                                    <img id="ccBrandLogoBack" class="cc-brand-logo" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
