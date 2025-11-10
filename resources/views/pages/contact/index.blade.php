{{-- resources/views/pages/contact/index.blade.php --}}
@extends('layouts.app')

@section('title', 'İletişim')

@section('content')
<section class="container py-4 py-lg-5">
    <div class="row g-4">
        {{-- SOL: Harita + iletişim bilgileri --}}
        <div class="col-lg-6">
            <h1 class="h4 mb-3">Bize Ulaşın</h1>

            {{-- Google Maps (Içmeler örnek) --}}
            <div class="ratio ratio-4x3 rounded overflow-hidden shadow-sm mb-3">
                <iframe
                    src="https://www.google.com/maps?q=I%C3%A7meler%2C%20Marmaris&hl=tr&z=13&output=embed"
                    allowfullscreen
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Harita">
                </iframe>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <i class="fi fi-rr-marker me-3 fs-4 text-primary"></i>
                        <div>
                            <div class="fw-semibold">Adres</div>
                            <div class="text-muted">Cumhuriyet Cd. No:12, İçmeler / Marmaris</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-3">
                        <i class="fi fi-rr-phone-call me-3 fs-4 text-primary"></i>
                        <div>
                            <div class="fw-semibold">Telefon</div>
                            <a href="tel:+902522223344">+90 252 222 33 44</a>
                        </div>
                    </div>

                    <div class="d-flex align-items-start mb-3">
                        <i class="fi fi-rr-envelope me-3 fs-4 text-primary"></i>
                        <div>
                            <div class="fw-semibold">E-posta</div>
                            <a href="mailto:destek@icmeleronline.com">destek@icmeleronline.com</a>
                        </div>
                    </div>

                    <div class="d-flex align-items-start">
                        <i class="fi fi-rr-clock-three me-3 fs-4 text-primary"></i>
                        <div>
                            <div class="fw-semibold">Çalışma Saatleri</div>
                            <div class="text-muted">Pzt–Cmt 09:00–19:00, Pazar 10:00–17:00</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SAĞ: İletişim formu --}}
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h2 class="h5 mb-3">İletişim Formu</h2>

                    <form action="#" method="POST" class="needs-validation" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Ad</label>
                                <input type="text" name="first_name" class="form-control" required autocomplete="given-name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Soyad</label>
                                <input type="text" name="last_name" class="form-control" required autocomplete="family-name">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">E-posta</label>
                                <input type="email" name="email" class="form-control" required autocomplete="email" inputmode="email" placeholder="ornek@mail.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telefon</label>
                                <input type="tel" name="phone" class="form-control" autocomplete="tel" inputmode="tel" placeholder="+90 5xx xxx xx xx">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Konu</label>
                                <input type="text" name="subject" class="form-control" required placeholder="Örn. Rezervasyon hakkında">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Mesajınız</label>
                                <textarea name="message" class="form-control" rows="5" required placeholder="Size nasıl yardımcı olabiliriz?"></textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="kvkk" required>
                                    <label class="form-check-label small" for="kvkk">
                                        KVKK ve gizlilik metnini okudum, kabul ediyorum.
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">
                                    Mesajı Gönder
                                </button>
                                <div class="small text-muted mt-2">Bu bir arayüz maketidir; form gönderimi yapılmaz.</div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection
