{{-- resources/views/pages/account/tickets.blade.php --}}
@extends('layouts.account')

@section('account_content')

{{-- FİLTRE / SIRALA BAR --}}
<div class="mb-3 border-bottom pb-3">
    <div class="row g-2 align-items-center">
        <div class="d-flex align-items-center justify-content-between gap-2 input-group-sm">
            <select id="sortSelect" class="form-select w-auto">
                <option value="date_desc">Tarih: Yeni → Eski</option>
                <option value="date_asc">Tarih: Eski → Yeni</option>
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

{{-- HOVER = Rezervasyonlar sayfasıyla aynı --}}
<style>
    .ticket-card .card-body{ cursor:pointer; border-radius:.5rem; }
    .ticket-card:hover .card-body,
    .ticket-card:focus-within .card-body{ background:rgba(0,0,0,.03); }
</style>

<div id="ticketList" class="vstack gap-3">
    <!-- Cevaplandı -->
    <div class="card shadow-sm position-relative ticket-card"
         data-when="2025-08-18T15:20:00"
         data-status="pending">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="fw-semibold">Rezervasyon iptal koşulları</div>
                    <div class="text-muted small mt-1">Grand Marina Hotel — Rezervasyon #A101</div>
                </div>
                <span class="badge bg-success">Cevaplandı</span>
            </div>
            <a href="{{ route('account.tickets.show', ['id' => 1042]) }}" class="stretched-link" aria-label="Talep detayına git: Rezervasyon iptal koşulları"></a>
        </div>
    </div>

    <!-- Okundu (rozet metni: Kapandı) -->
    <div class="card shadow-sm position-relative ticket-card"
         data-when="2025-08-12T10:05:00"
         data-status="canceled">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="fw-semibold">Fatura bilgisi güncelleme</div>
                    <div class="text-muted small mt-1">Ege Sunset Apart — Rezervasyon #B204</div>
                </div>
                <span class="badge bg-dark">Kapandı</span>
            </div>
            <a href="{{ route('account.tickets.show', ['id' => 1042]) }}" class="stretched-link" aria-label="Talep detayına git: Fatura bilgisi güncelleme"></a>
        </div>
    </div>

    <!-- Yanıt Bekliyor -->
    <div class="card shadow-sm position-relative ticket-card"
         data-when="2025-08-10T09:12:00"
         data-status="completed">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="fw-semibold">Oda tipinde değişiklik talebi</div>
                    <div class="text-muted small mt-1">Maris Deluxe Resort — Rezervasyon #C332</div>
                </div>
                <span class="badge bg-info">Yanıt Bekliyor</span>
            </div>
            <a href="{{ route('account.tickets.show', ['id' => 1042]) }}" class="stretched-link" aria-label="Talep detayına git: Oda tipinde değişiklik talebi"></a>
        </div>
    </div>

    <!-- Kapandı -->
    <div class="card shadow-sm position-relative ticket-card"
         data-when="2025-08-05T18:40:00"
         data-status="canceled">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="fw-semibold">Transfer saatinde revizyon</div>
                    <div class="text-muted small mt-1">Dalaman Havalimanı → İçmeler Transferi — PNR #TRX4581</div>
                </div>
                <span class="badge bg-dark">Kapandı</span>
            </div>
            <a href="{{ route('account.tickets.show', ['id' => 1042]) }}" class="stretched-link" aria-label="Talep detayına git: Transfer saatinde revizyon"></a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('ticketList');
        const sortSelect = document.getElementById('sortSelect');
        const statusFilter = document.getElementById('statusFilter');

        function getCards(){ return Array.from(list.querySelectorAll('.ticket-card')); }
        function parseWhen(c){ const t = Date.parse(c.getAttribute('data-when')||''); return isNaN(t)?0:t; }

        function applyFilter(cards){
            const wanted = statusFilter.value; // all | pending | completed | canceled
            cards.forEach(c=>{
                const st = (c.getAttribute('data-status')||'').toLowerCase();
                c.classList.toggle('d-none', !(wanted==='all'||st===wanted));
            });
        }

        function applySort(cards){
            const mode = sortSelect.value; // date_desc | date_asc
            const visible = cards.filter(c=>!c.classList.contains('d-none'));
            visible.sort((a,b)=> (mode==='date_asc' ? parseWhen(a)-parseWhen(b) : parseWhen(b)-parseWhen(a)));
            const frag = document.createDocumentFragment();
            visible.forEach(c=>frag.appendChild(c));
            list.prepend(frag);
        }

        function refresh(){ const cards=getCards(); applyFilter(cards); applySort(cards); }

        sortSelect.addEventListener('change', refresh);
        statusFilter.addEventListener('change', refresh);
        refresh();
    });
</script>

@endsection
