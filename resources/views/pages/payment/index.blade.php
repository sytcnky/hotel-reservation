{{-- resources/views/pages/payment/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Ödeme')

@section('content')
<section class="container py-4 py-lg-5">
    <div class="row g-4">
        <!-- SOL: Ödeme Formu (8) -->
        <div class="col-lg-8">
            <h1 class="h4 mb-3">Ödeme Bilgileri</h1>

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

            <!-- Form -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Kart Üzerindeki İsim</label>
                            <input type="text" class="form-control" id="ccHolder" placeholder="Ad Soyad" maxlength="30" autocomplete="cc-name">
                        </div>

                        <div class="col-12">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span>Kart Numarası</span>
                                <span class="small" id="ccLuhnState">
                  <i class="fi fi-rr-credit-card"></i>
                </span>
                            </label>
                            <input type="text" class="form-control" id="ccNumber" inputmode="numeric" autocomplete="cc-number" placeholder="•••• •••• •••• ••••" aria-describedby="ccHelp">
                            <div id="ccHelp" class="form-text">Visa, MasterCard, Amex, Troy desteklenir.</div>
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label">Son Kullanma</label>
                            <input type="text" class="form-control" id="ccExpiry" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/YY" maxlength="5">
                        </div>

                        <div class="col-6 col-md-4">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span>CVV</span>
                                <i class="fi fi-rr-info" data-bs-toggle="tooltip" title="Kartınızın arka yüzündeki 3-4 haneli güvenlik kodu."></i>
                            </label>
                            <input type="text" class="form-control" id="ccCvv" inputmode="numeric" autocomplete="cc-csc" placeholder="•••" maxlength="3">
                            <div class="form-text">CVV alanına odaklanınca kartın arka yüzü gösterilir.</div>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms">
                                <label class="form-check-label" for="terms">
                                    Ön bilgilendirme ve mesafeli satış sözleşmesini okudum, kabul ediyorum.
                                </label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button class="btn btn-primary w-100" type="button">Ödemeyi Tamamla</button>
                            <div class="small text-muted mt-2">Bu bir arayüz önizlemesidir; gerçek ödeme yapılmaz.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SAĞ: Sipariş Özeti (4) -->
        <div class="col-lg-4">
            <div class="position-sticky" style="top: 90px;">
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h2 class="h6 mb-3">Sipariş Özeti</h2>
                        <div class="d-flex justify-content-between small mb-2">
                            <span>Ara toplam</span><span>₺35.250</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span>Vergiler</span><span>₺0</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-2">
                            <span>İndirimler</span><span>₺0</span>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Ödenecek Toplam</span>
                            <span class="fw-bold fs-5">₺35.250</span>
                        </div>
                    </div>
                </div>
                <div class="card border-0 bg-light">
                    <div class="card-body py-3 small text-muted">
                        Ödeme sırasında fiyatlar yeniden doğrulanır ve güvence altına alınır.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Sayfa içi minimal stil (UI-only) --}}
<style>
    .cc{ width:100%; max-width:420px; height:260px; perspective:1000px; margin:0 auto 1rem; }
    .cc-inner{ position:relative; width:100%; height:100%; transform-style:preserve-3d; transition:transform .6s ease; }
    .cc-face {
        position:absolute; inset:0; border-radius:16px; color:#fff; padding:20px;
        backface-visibility:hidden; overflow:hidden;
        /* üstte parlama, altta hafif gölge, en altta ana degrade */
        background:
            radial-gradient(120% 120% at 10% 0%, rgba(255,255,255,.18), rgba(255,255,255,0) 55%),
            radial-gradient(120% 120% at 100% 100%, rgba(0,0,0,.18), rgba(0,0,0,0) 55%),
            linear-gradient(150deg, #6a33c7 0%, #5b3ecc 28%, #4a4fd7 58%, #3e61e4 85%, #3867e9 100%),
                /* ultra hafif grain */
            repeating-linear-gradient(0deg, rgba(255,255,255,.02) 0 1px, rgba(0,0,0,0) 1px 2px);
        box-shadow: 0 10px 30px rgba(0,0,0,.15);
    }
    .cc-front .cc-top{ display:flex; justify-content:space-between; align-items:center; }
    .cc-chip{ width:44px; height:32px; border-radius:6px; background:linear-gradient(180deg,#f6d365,#fda085); box-shadow:inset 0 0 0 1px rgba(0,0,0,.1); }
    .cc-brand-logo{ height:24px; width:auto; display:none; } /* unknown -> gizli */
    .cc-number{ font:600 26px/1.3 ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing:2px; margin:28px 0; }
    .cc-label{ font-size:11px; opacity:.85; letter-spacing:.6px; }
    .cc-value{ font-weight:600; font-size:14px; text-transform:uppercase; }
    .cc-bottom{ display:flex; justify-content:space-between; align-items:flex-end; }

    .cc-back{
        transform: rotateY(180deg);
        background:
            radial-gradient(110% 110% at 100% 0%, rgba(255,255,255,.10), rgba(255,255,255,0) 50%),
            linear-gradient(150deg, #2a2a3a 0%, #32324a 55%, #3a3a56 100%),
            repeating-linear-gradient(0deg, rgba(255,255,255,.02) 0 1px, rgba(0,0,0,0) 1px 2px);
    }
    .cc-strip{ height:48px; background:#111; margin-top:18px; margin-bottom:28px; }
    .cc-cvv-box{ background:#fff; color:#111; padding:8px 10px; border-radius:6px; width:120px; text-align:right; margin-left:auto; }
</style>
@endsection
