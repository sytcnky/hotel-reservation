@extends('layouts.account', ['pageKey' => 'account-tickets'])
@section('account_content')

    @php
        $tickets = $tickets ?? collect();
    @endphp

    @if($tickets->isEmpty())
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div class="fw-semibold mb-1">Henüz destek talebinde bulunmadınız.</div>
                <div class="text-muted small mb-3">Bir sorun yaşarsanız buradan yeni talep oluşturabilirsiniz.</div>

                <a href="{{ localized_route('account.tickets.create') }}" class="btn btn-success">
                    Yeni Talep Oluştur
                </a>
            </div>
        </div>
    @else
        <div class="p-3 bg-light mb-3 rounded">
            <div class="d-flex justify-content-between flex-column flex-lg-row">
                <div>
                    <h5 class="m-0 text-secondary">Yardıma mı ihtiyacınız var?</h5>
                    <p class="m-0 text-secondary">Talep oluşturup sorununuzu bizimle paylaşın.</p>
                </div>
                <a href="{{ localized_route('account.tickets.create') }}" class="btn btn-success mt-3 align-self-lg-center">
                    Yeni Talep Oluştur
                </a>
            </div>
        </div>

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
                        <option value="waiting_customer">Yanıtlandı</option>
                        <option value="waiting_agent">Yanıt Bekliyor</option>
                        <option value="closed">Kapalı</option>
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
            @foreach($tickets as $ticket)
                <div class="card shadow-sm position-relative ticket-card"
                     data-when="{{ optional($ticket->last_message_at)->toIso8601String() }}"
                     data-status="{{ $ticket->status }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-lg-8">
                                <div class="fw-semibold">{{ $ticket->subject }}</div>
                                <div class="mt-1">
                                    <span class="badge text-bg-light fw-normal">
                                        {{ $ticket->category?->name_l ?? '-' }}
                                    </span>

                                    @if($ticket->order_id)
                                        <span class="badge text-bg-light fw-normal">
                                            {{ $ticket->order?->code ?? $ticket->order_id }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-lg-4 text-end d-none d-lg-block">
                                @if($ticket->status === 'waiting_agent')
                                    <span class="badge bg-warning-subtle text-warning">Yanıt Bekliyor</span>
                                @elseif($ticket->status === 'waiting_customer')
                                    <span class="badge bg-success-subtle text-success">Yanıtlandı</span>
                                @elseif($ticket->status === 'closed')
                                    <span class="badge bg-secondary-subtle text-secondary">Kapalı</span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ localized_route('account.tickets.show', ['ticket' => $ticket->id]) }}"
                           class="stretched-link"
                           aria-label="Talep detayına git: {{ $ticket->subject }}"></a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
