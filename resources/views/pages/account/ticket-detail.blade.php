{{-- resources/views/pages/account/ticket-detail.blade.php --}}
@extends('layouts.account')

@section('account_content')

{{-- Başlık / meta --}}
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
                <h2 class="h5 mb-1">Rezervasyon iptal koşulları</h2>
                <div class="text-muted small">
                    Grand Marina Hotel — Rezervasyon #A101
                </div>
            </div>
            <span class="badge bg-info text-dark align-self-start">Yanıt Bekliyor</span>
        </div>

        <hr class="my-3">

        <dl class="row mb-0 small text-muted">
            <dt class="col-sm-3">Talep No</dt>
            <dd class="col-sm-9">#{{ $id }}</dd>

            <dt class="col-sm-3">Oluşturulma</dt>
            <dd class="col-sm-9">18 Ağustos 2025 • 14:00</dd>

            <dt class="col-sm-3">Son Güncelleme</dt>
            <dd class="col-sm-9">18 Ağustos 2025 • 15:20</dd>

            <dt class="col-sm-3">Kategori</dt>
            <dd class="col-sm-9">Rezervasyon</dd>
        </dl>
    </div>
</div>

{{-- Mesaj akışı --}}
<div class="vstack gap-3">
    {{-- Kullanıcı mesajı --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="fw-semibold">Siz</div>
                <div class="text-muted small">18.08.2025 • 14:00</div>
            </div>
            <p class="mb-0">İptal süresi ve kesinti oranları nedir?</p>
        </div>
    </div>

    {{-- Agent yanıtı --}}
    <div class="card shadow-sm bg-body-tertiary">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="fw-semibold">Destek Ekibi</div>
                <div class="text-muted small">18.08.2025 • 14:30</div>
            </div>
            <p class="mb-0">
                Girişten 7 gün öncesine kadar ücretsiz iptal mümkündür. Sonrasında ilk gece ücreti kesilir.
            </p>
        </div>
    </div>

    {{-- Kullanıcı ek mesajı --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div class="fw-semibold">Siz</div>
                <div class="text-muted small">18.08.2025 • 15:20</div>
            </div>
            <p class="mb-0">
                Anladım, teşekkürler. Rezervasyonumu iptal etmek istersem süreci buradan mı başlatmalıyım?
            </p>
        </div>
    </div>
</div>

{{-- Yanıt formu --}}
<div class="card shadow-sm mt-3">
    <div class="card-body">
        <form action="#" method="post">
            <div class="mb-3">
                <label for="replyMessage" class="form-label">Yanıtınız</label>
                <textarea id="replyMessage" name="message" class="form-control" rows="4" placeholder="Mesajınızı yazın..."></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Gönder</button>
        </form>
    </div>
</div>

{{-- Geri bağlantısı --}}
<div class="mt-4">
    <a href="{{ route('account.tickets') }}" class="btn btn-outline-secondary">
        ← Tüm taleplere dön
    </a>
</div>

@endsection
