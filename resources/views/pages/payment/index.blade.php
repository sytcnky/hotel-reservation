{{-- resources/views/pages/payment/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Ödeme')

@section('content')
<section class="container py-4 py-lg-5">
    <div class="row g-4">
        <!-- SOL: Ödeme Formu (8) -->
        <div class="col-lg-7">
            <h1 class="h4 mb-3">Ödeme Bilgileri</h1>

            <h3 class="text-muted mb-3">
                Ödenecek Tutar:
                <span class="fw-semibold text-body">₺35.250</span>
            </h3>

            <!-- Form -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="post" id="paymentForm" autocomplete="on" novalidate>
                        <div class="row g-3">

                            <!-- Kart Sahibi -->
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

                            <!-- Kart Numarası -->
                            <div class="col-12">
                                <label class="form-label">Kart Numarası</label>

                                <div class="input-group">
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
                                </div>

                                <div class="invalid-feedback">
                                    Lütfen geçerli bir kart numarası girin.
                                </div>
                            </div>

                            <!-- SKT (MM/YY) -->
                            <div class="col-6 col-md-4">
                                <label class="form-label">Son Kullanma</label>

                                <div class="input-group">
                                    <!-- AY -->
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

                                    <!-- YIL -->
                                    <input type="text"
                                           class="form-control"
                                           id="ccExpiryYear"
                                           name="exp-year"
                                           inputmode="numeric"
                                           autocomplete="cc-exp-year"
                                           placeholder="YY"
                                           maxlength="2"
                                           required>
                                </div>

                                <div class="invalid-feedback">
                                    Lütfen geçerli bir son kullanma tarihi girin (AA / YY).
                                </div>
                            </div>


                            <!-- CVV -->
                            <div class="col-6 col-md-4">
                                <label class="form-label d-flex justify-content-between align-items-center">
                                    <span>CVV</span>
                                    <i class="fi fi-rr-info"
                                       data-bs-toggle="tooltip"
                                       title="Kartınızın arka yüzündeki 3-4 haneli güvenlik kodu."></i>
                                </label>

                                <div class="input-group">
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
                                </div>

                                <div class="invalid-feedback">
                                    Lütfen kartınızın 3 veya 4 haneli güvenlik kodunu girin.
                                </div>
                            </div>

                            <!-- Sözleşme -->
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

                            <!-- Submit -->
                            <div class="col-12">
                                <button class="btn btn-primary w-100"
                                        type="button"
                                        id="btnSubmitPayment"
                                        disabled>
                                    Ödemeyi Tamamla
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- SAĞ: Sipariş Özeti (4) -->
        <div class="col-lg-5">

            <!-- Kart Önizleme -->
            <div class="mb-4">
                <div class="cc">
                    <div class="cc-inner" id="ccInner">
                        <!-- FRONT -->
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

                        <!-- BACK -->
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
