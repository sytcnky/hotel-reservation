@extends('layouts.account')

@section('account_content')
<h1 class="display-5 text-center text-secondary d-block d-lg-none">Rezervasyonlarım</h1>

{{-- FİLTRE / SIRALA BAR --}}
<div class="mb-3 border-bottom pb-3">
    <div class="row g-2 align-items-center">
        {{-- Solda: Sırala --}}
        <div class="d-flex align-items-center justify-content-between gap-2 input-group-sm">
            <select id="sortSelect" class="form-select w-auto">
                <option value="all">Tümü</option>
                <option value="date_desc">Hotel</option>
                <option value="date_asc">Transfer</option>
                <option value="price_asc">Villa</option>
                <option value="price_desc">Tur</option>
            </select>

            <select id="statusFilter" class="form-select w-auto">
                <option value="all">Tümü</option>
                <option value="pending">Cevaplandı</option>
                <option value="completed">Yanıt Bekliyor</option>
                <option value="canceled">Kapandı</option>
            </select>
        </div>
    </div>
</div>

{{-- UI DEMO: Rezervasyon Kartı (bağımsız, açılır) --}}
<style>
    .booking-thumb {
        width: 96px;
        height: 72px;
        object-fit: cover;
        object-position: left;
        border-radius: .5rem
    }

    .booking-toggle {
        cursor: pointer;
        border-radius: .5rem
    }

    .booking-toggle:hover {
        background: rgba(0, 0, 0, .03)
    }

    .booking-meta {
        font-size: .925rem
    }
</style>

<div id="bookingList">
    {{-- HOTEL --}}
    <div class="card booking-card mb-3"
         data-when="2025-09-12"
         data-price="18750"
         data-status="pending">
        <div class="card-body align-items-center g-3 booking-toggle p-3"
             data-bs-toggle="collapse"
             data-bs-target="#booking-101-A101"
             role="button"
             aria-expanded="false"
             aria-controls="booking-101-A101">
            {{-- ÜST SATIR (TIKLANABİLİR) --}}
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="/images/samples/room-1.jpg" class="booking-thumb" alt="küçük görsel">
                </div>
                <div class="col p-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-semibold">Grand Marina Hotel</span>
                        <span class="text-muted">— 101-A101</span>
                    </div>
                    <div class="text-muted booking-meta">
                        <span class="badge bg-secondary text-light align-middle my-1">Otel</span>
                        <span class="badge bg-secondary-subtle text-secondary align-middle my-1">2 Yetişkin</span>
                        <span class="badge bg-secondary-subtle text-secondary align-middle my-1">1 Çocuk</span>
                    </div>
                    <div class="text-muted booking-meta align-items-center d-flex">
                        12 Eylül 2025 - 18 Eylül 2025
                    </div>
                </div>
                <div class="col-auto text-end align-self-start">
                    <span class="badge bg-warning">Onay Bekliyor...</span>
                </div>
            </div>

            {{-- ALT DETAY (AÇILAN ALAN) --}}
            <div id="booking-101-A101" class="collapse">
                <hr>
                <div class="row">
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Sipariş no</dt>
                            <dd class="col-sm-8"><code>101-A101</code></dd>

                            <dt class="col-sm-4">Oluşturulma tarihi</dt>
                            <dd class="col-sm-8">12 Eylül 2025</dd>

                            <dt class="col-sm-4">Misafir</dt>
                            <dd class="col-sm-8">2 yetişkin, 1 çocuk</dd>

                            <dt class="col-sm-4">Oda tipi</dt>
                            <dd class="col-sm-8">Standart Oda</dd>

                            <dt class="col-sm-4">Ücret</dt>
                            <dd class="col-sm-8">18.750 ₺</dd>
                        </dl>
                    </div>
                    <div class="col-12">
                        <hr>
                    </div>
                    <div class="col-12 d-flex align-items-start justify-content-between gap-2">
                        <a href="/support/tickets/ABC-123" class="btn btn-outline-secondary">İptal et</a>
                        <a href="#" class="btn btn-outline-primary">Destek talebi oluştur</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TRANSFER --}}
    <div class="card booking-card mb-3"
         data-when="2025-08-25T10:30"
         data-price="1200"
         data-status="completed">
        <div class="card-body align-items-center g-3 booking-toggle p-3"
             data-bs-toggle="collapse"
             data-bs-target="#booking-101-B101"
             role="button"
             aria-expanded="false"
             aria-controls="booking-101-B101">
            {{-- ÜST SATIR (TIKLANABİLİR) --}}
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="/images/vito.png" class="booking-thumb" alt="küçük görsel">
                </div>
                <div class="col p-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-semibold">Havalimanı Transferi (Vito)</span>
                        <span class="text-muted">— 101-B101</span>
                    </div>
                    <div class="text-muted booking-meta">
                        <span class="badge bg-secondary text-light align-middle my-1">Transfer</span>
                        <span class="badge bg-secondary-subtle text-secondary align-middle my-1">2 Yetişkin</span>
                        <span class="badge bg-secondary-subtle text-secondary align-middle my-1">1 Çocuk</span>
                    </div>
                    <div class="text-muted booking-meta align-items-center d-flex">
                        25 Ağustos 2025 • 10:30
                    </div>
                </div>
                <div class="col-auto text-end align-self-start">
                    <span class="badge bg-success">Onaylandı</span>
                </div>
            </div>

            {{-- ALT DETAY (AÇILAN ALAN) --}}
            <div id="booking-101-B101" class="collapse">
                <hr>
                <div class="row">
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Sipariş no</dt>
                            <dd class="col-sm-8"><code>101-B101</code></dd>

                            <dt class="col-sm-4">Oluşturulma tarihi</dt>
                            <dd class="col-sm-8">10.05.2025</dd>

                            <dt class="col-sm-4">Rota</dt>
                            <dd class="col-sm-8">Dalaman → İçmeler</dd>

                            <dt class="col-sm-4">Yolcu</dt>
                            <dd class="col-sm-8">2 yetişkin, 1 çocuk</dd>

                            <dt class="col-sm-4">Araç</dt>
                            <dd class="col-sm-8">Mercedes Vito</dd>

                            <dt class="col-sm-4">Ücret</dt>
                            <dd class="col-sm-8">1.200 ₺</dd>
                        </dl>
                    </div>
                    <div class="col-12">
                        <hr>
                    </div>
                    <div class="col-12 d-flex align-items-start justify-content-between gap-2">
                        <a href="/support/tickets/ABC-456" class="btn btn-outline-secondary">İptal et</a>
                        <a href="#" class="btn btn-outline-primary">Destek talebi oluştur</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TUR --}}
    <div class="card booking-card mb-3"
         data-when="2025-08-27"
         data-price="950"
         data-status="canceled">
        <div class="card-body align-items-center g-3 booking-toggle p-3"
             data-bs-toggle="collapse"
             data-bs-target="#booking-101-C101"
             role="button"
             aria-expanded="false"
             aria-controls="booking-101-C101">
            {{-- ÜST SATIR (TIKLANABİLİR) --}}
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="/images/k2.webp" class="booking-thumb" alt="küçük görsel">
                </div>
                <div class="col p-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-semibold">Dalyan Tekne Turu</span>
                        <span class="text-muted">— 101-C101</span>
                    </div>
                    <div class="text-muted booking-meta">
                        <span class="badge bg-secondary text-light align-middle my-1">Tur</span>
                        <span class="badge bg-secondary-subtle text-secondary align-middle my-1">1 Yetişkin</span>
                        <span class="badge bg-secondary-subtle text-secondary align-middle my-1">1 Çocuk</span>
                    </div>
                    <div class="text-muted booking-meta align-items-center d-flex">
                        27 Ağustos 2025
                    </div>
                </div>
                <div class="col-auto text-end align-self-start">
                    <span class="badge bg-secondary">İptal edildi</span>
                </div>
            </div>

            {{-- ALT DETAY (AÇILAN ALAN) --}}
            <div id="booking-101-C101" class="collapse">
                <hr>
                <div class="row">
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Sipariş no</dt>
                            <dd class="col-sm-8"><code>101-C101</code></dd>

                            <dt class="col-sm-4">Oluşturulma tarihi</dt>
                            <dd class="col-sm-8">08.05.2025</dd>

                            <dt class="col-sm-4">Katılımcı</dt>
                            <dd class="col-sm-8">2 yetişkin</dd>

                            <dt class="col-sm-4">Tur tarihi</dt>
                            <dd class="col-sm-8">27.08.2025</dd>

                            <dt class="col-sm-4">Ücret</dt>
                            <dd class="col-sm-8">950 ₺</dd>
                        </dl>
                    </div>
                    <div class="col-12">
                        <hr>
                    </div>
                    <div class="col-12 d-flex align-items-start justify-content-between gap-2">
                        <a href="#" class="btn btn-outline-primary">Destek talebi oluştur</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- VILLA --}}
    <div class="card booking-card mb-3"
         data-when="2025-10-05"
         data-price="42000"
         data-status="completed">
        <div class="card-body align-items-center g-3 booking-toggle p-3"
             data-bs-toggle="collapse"
             data-bs-target="#booking-101-D101"
             role="button"
             aria-expanded="false"
             aria-controls="booking-101-D101">
            {{-- ÜST SATIR (TIKLANABİLİR) --}}
            <div class="row align-items-center">
                <div class="col-auto">
                    <img src="/images/samples/villa-sample-1.jpg" class="booking-thumb" alt="küçük görsel">
                </div>
                <div class="col p-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="fw-semibold">Sunset Villa</span>
                        <span class="text-muted">— 101-D101</span>
                    </div>
                    <div class="text-muted booking-meta">
                        <span class="badge bg-secondary text-light align-middle my-1">Villa</span>
                        <span class="badge bg-secondary-subtle text-secondary align-middle my-1">4 Kişi</span>
                    </div>
                    <div class="text-muted booking-meta align-items-center d-flex">
                        05 Ekim 2025 - 12 Ekim 2025
                    </div>
                </div>
                <div class="col-auto text-end align-self-start">
                    <span class="badge bg-success">Onaylandı</span>
                </div>
            </div>

            {{-- ALT DETAY (AÇILAN ALAN) --}}
            <div id="booking-101-D101" class="collapse">
                <hr>
                <div class="row">
                    <div class="col-12">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Sipariş no</dt>
                            <dd class="col-sm-8"><code>101-D101</code></dd>

                            <dt class="col-sm-4">Oluşturulma tarihi</dt>
                            <dd class="col-sm-8">12.06.2025</dd>

                            <dt class="col-sm-4">Giriş/Çıkış</dt>
                            <dd class="col-sm-8">05.10.2025 — 12.10.2025</dd>

                            <dt class="col-sm-4">Misafir</dt>
                            <dd class="col-sm-8">4 Yetişkin</dd>

                            <dt class="col-sm-4">Ücret</dt>
                            <dd class="col-sm-8">42.000 ₺</dd>
                        </dl>
                    </div>
                    <div class="col-12">
                        <hr>
                    </div>
                    <div class="col-12 d-flex align-items-start justify-content-between gap-2">
                        <a href="/support/tickets/ABC-987" class="btn btn-outline-secondary">İptal et</a>
                        <a href="#" class="btn btn-outline-primary">Destek talebi oluştur</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('bookingList');
        const sortSelect = document.getElementById('sortSelect');
        const statusFilter = document.getElementById('statusFilter');

        function getCards() {
            return Array.from(list.querySelectorAll('.booking-card'));
        }

        function parseWhen(card) {
            const v = card.getAttribute('data-when') || '';
            const t = Date.parse(v);
            return isNaN(t) ? 0 : t;
        }

        function parsePrice(card) {
            const v = parseInt(card.getAttribute('data-price'), 10);
            return isNaN(v) ? 0 : v;
        }

        function applyFilter(cards) {
            const wanted = statusFilter.value; // all | completed | pending | canceled
            cards.forEach(c => {
                const st = (c.getAttribute('data-status') || '').toLowerCase();
                const show = (wanted === 'all') || (st === wanted);
                c.classList.toggle('d-none', !show);
            });
        }

        function applySort(cards) {
            // açık collapseları kapat (reflow bozulmasın)
            list.querySelectorAll('.collapse.show').forEach(el => {
                const acc = bootstrap.Collapse.getOrCreateInstance(el);
                acc.hide();
            });

            const mode = sortSelect.value; // date_desc | date_asc | price_asc | price_desc | all
            const visible = cards.filter(c => !c.classList.contains('d-none'));

            visible.sort((a, b) => {
                if (mode === 'date_desc')  return parseWhen(b)  - parseWhen(a);
                if (mode === 'date_asc')   return parseWhen(a)  - parseWhen(b);
                if (mode === 'price_asc')  return parsePrice(a) - parsePrice(b);
                if (mode === 'price_desc') return parsePrice(b) - parsePrice(a);
                return 0;
            });

            const frag = document.createDocumentFragment();
            visible.forEach(c => frag.appendChild(c));
            list.prepend(frag);
        }

        function refresh() {
            const cards = getCards();
            applyFilter(cards);
            applySort(cards);
        }

        sortSelect.addEventListener('change', refresh);
        statusFilter.addEventListener('change', refresh);

        // İlk yüklemede uygula
        refresh();
    });
</script>

@endsection
