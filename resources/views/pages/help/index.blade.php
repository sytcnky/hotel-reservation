{{-- resources/views/pages/help/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Yardım & SSS')

@section('content')
<section class="container py-4 py-lg-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="h4 mb-2">Yardım & Sık Sorulan Sorular</h1>
            <p class="text-muted mb-4">Aradığını bulamazsan <a href="{{ route('contact') }}">bize ulaş</a>.</p>


            <!-- Arama -->
            <div class="input-group mb-4">
                <span class="input-group-text"><i class="fi fi-rr-search"></i></span>
                <input type="search" class="form-control" id="faqSearch" placeholder="Ara: ödeme, iptal, fatura..." aria-label="SSS içinde ara">
                <button class="btn btn-outline-secondary d-none" type="button" id="faqClear">Temizle</button>
            </div>

            <!-- Sonuç yok mesajı -->
            <div id="faqEmpty" class="alert alert-light border d-none" role="status">
                Aramanızla eşleşen sonuç bulunamadı.
            </div>


            <div class="accordion" id="faqAccordion">
                {{-- Soru 1 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q1">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#a1" aria-expanded="true" aria-controls="a1">
                            Ödemem onaylandı mı, rezervasyonum kesinleşti mi?
                        </button>
                    </h2>
                    <div id="a1" class="accordion-collapse collapse show" aria-labelledby="q1" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Ödeme başarıyla tamamlandığında rezervasyonunuz oluşturulur ve onay e-postası gönderilir. “Hesabım &rarr; Rezervasyonlarım” sekmesinden durumunu görebilirsiniz.
                        </div>
                    </div>
                </div>

                {{-- Soru 2 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q2">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a2" aria-expanded="false" aria-controls="a2">
                            Rezervasyonumu nasıl iptal ederim?
                        </button>
                    </h2>
                    <div id="a2" class="accordion-collapse collapse" aria-labelledby="q2" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            “Hesabım &rarr; Rezervasyonlarım” sayfasından ilgili rezervasyonu açıp <strong>İptal Et</strong> talebi oluşturabilirsiniz. İptal koşulları otele/ürüne göre değişebilir.
                        </div>
                    </div>
                </div>

                {{-- Soru 3 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q3">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a3" aria-expanded="false" aria-controls="a3">
                            Tarih veya kişi sayısını nasıl değiştirebilirim?
                        </button>
                    </h2>
                    <div id="a3" class="accordion-collapse collapse" aria-labelledby="q3" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Rezervasyon detayından <strong>Değişiklik Talebi</strong> oluşturabilirsiniz. Uygunluk ve fiyat farkları tedarikçiye göre oluşabilir.
                        </div>
                    </div>
                </div>

                {{-- Soru 4 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q4">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a4" aria-expanded="false" aria-controls="a4">
                            Kurumsal fatura alabilir miyim?
                        </button>
                    </h2>
                    <div id="a4" class="accordion-collapse collapse" aria-labelledby="q4" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Evet. Sepet/Ödeme özetinde <strong>Kurumsal fatura istiyorum</strong> kutucuğunu işaretleyip firma adı, vergi dairesi ve vergi no bilgilerini girmeniz yeterli.
                        </div>
                    </div>
                </div>

                {{-- Soru 5 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q5">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a5" aria-expanded="false" aria-controls="a5">
                            Üye olmadan sipariş verdim; rezervasyonumu nerede görürüm?
                        </button>
                    </h2>
                    <div id="a5" class="accordion-collapse collapse" aria-labelledby="q5" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Onay e-postasındaki bağlantıyla rezervasyon detayına erişebilirsiniz. Dilerseniz e-postadaki “Hesap oluştur” bağlantısıyla kaydolup tüm rezervasyonları tek yerde yönetebilirsiniz.
                        </div>
                    </div>
                </div>

                {{-- Soru 6 --}}
                <div class="accordion-item">
                    <h2 class="accordion-header" id="q6">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#a6" aria-expanded="false" aria-controls="a6">
                            Ödeme yöntemleriniz neler?
                        </button>
                    </h2>
                    <div id="a6" class="accordion-collapse collapse" aria-labelledby="q6" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Kredi/Banka kartı (Visa, MasterCard, Amex/TROY destekli bankalar) ile ödeme alıyoruz. Bazı ürünlerde ön ödeme + tesiste ödeme seçeneği bulunabilir.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Alt CTA --}}
            <div class="card bg-light border-0 mt-4">
                <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between">
                    <div class="text-muted mb-2 mb-md-0">
                        Hâlâ yardıma mı ihtiyacın var?
                    </div>
                    <div class="d-flex gap-2">

                        <a href="https://wa.me/905551112233" target="_blank"
                           class="btn btn-outline-success btn-sm text-decoration-none">
                            <i class="fi fi-brands-whatsapp fs-5 align-middle"></i>
                            <span>Whatsapp Destek</span>
                        </a>
                        <a href="{{ route('contact') }}" class="btn btn-outline-primary">
                            Bize Ulaşın
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
