@extends('layouts.account')

@section('account_content')

    @php
        /** @var \Illuminate\Support\Collection|\App\Models\Order[] $orders */
        $orders = $orders ?? collect();

        $statusList = \App\Models\Order::statusList();

        $statusFilterOptions = [
            'all' => 'Tümü',
        ];

        foreach ($statusList as $st) {
            $statusFilterOptions[$st] = \App\Models\Order::statusMeta($st)['label'] ?? strtoupper((string) $st);
        }
    @endphp

    {{-- FİLTRE / SIRALA BAR --}}
    <div class="mb-3 border-bottom pb-3">
        <div class="row g-2 align-items-center">
            <div class="d-flex align-items-center justify-content-between gap-2 input-group-sm flex-wrap">
                {{-- Ortada: Status --}}
                <select id="statusFilter" class="form-select w-auto">
                    @foreach($statusFilterOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                {{-- Sağda: Sırala --}}
                <select id="sortSelect" class="form-select w-auto">
                    <option value="date_desc">Tarih (Yeni → Eski)</option>
                    <option value="date_asc">Tarih (Eski → Yeni)</option>
                    <option value="price_desc">Tutar (Yüksek → Düşük)</option>
                    <option value="price_asc">Tutar (Düşük → Yüksek)</option>
                </select>
            </div>
        </div>
    </div>

    <style>
        @media (min-width: 992px) {
            .booking-thumb {
                max-width: 160px;
                height: 110px;
                object-fit: cover;
                object-position: left;
                border-radius: .5rem
            }
        }
        .booking-thumb {
            min-width: 160px;
            width: 100%;
            max-height: 120px;
            object-fit: cover;
            object-position: left;
            border-radius: .5rem
        }
        .booking-toggle { cursor: pointer; border-radius: .5rem }
        .booking-toggle:hover { background: rgba(0, 0, 0, .03) }
        .booking-meta { font-size: .925rem }
    </style>

    <div id="bookingList">

        @if($orders->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Henüz bir rezervasyonunuz yok.</div>
                </div>
            </div>
        @endif

        @foreach($orders as $order)
            @php
                $items = collect($order->items_for_infolist ?? []);

                $types = $items->pluck('type')
                    ->filter()
                    ->map(fn ($t) => (string) $t)
                    ->unique()
                    ->values();

                $status = strtolower((string) ($order->status ?? \App\Models\Order::STATUS_PENDING));
                $meta = \App\Models\Order::statusMeta($status);

                $statusLabel = (string) ($meta['label'] ?? $status);
                $statusClass = (string) ($meta['bootstrap_class'] ?? 'bg-secondary');

                $currency = $order->currency ?? null;
                $totalText = \App\Support\Currency\CurrencyPresenter::format($order->total_amount ?? null, $currency);

                $whenIso   = optional($order->created_at)->format('Y-m-d') ?? '';
                $whenHuman = optional($order->created_at)->translatedFormat('d M Y') ?? '';

                $collapseId = 'booking-' . $order->id;

                $discounts = collect($order->discounts_for_infolist ?? []);

                if ($order->relationLoaded('refundAttempts')) {
                    $refundRows = collect($order->refundAttempts ?? [])->map(function ($r) use ($currency) {
                        $amountText = \App\Support\Currency\CurrencyPresenter::format($r->amount ?? null, $currency);

                        return [
                            'reason' => $r->reason ?: null,
                            'amount' => $amountText,
                            'time'   => $r->created_at?->format('d.m.Y H:i') ?? null,
                        ];
                    });
                } else {
                    $refundRows = collect($order->refunds_for_infolist ?? [])->map(function ($r) {
                        return [
                            'reason' => $r['reason'] ?? null,
                            'amount' => $r['amount'] ?? null,
                            'time'   => $r['time'] ?? null,
                        ];
                    });
                }
            @endphp

            <div class="card booking-card mb-3"
                 data-when="{{ $whenIso }}"
                 data-price="{{ (int) round((float) ($order->total_amount ?? 0)) }}"
                 data-status="{{ $status }}">

                <div class="card-body align-items-center g-3 booking-toggle p-3"
                     data-bs-toggle="collapse"
                     data-bs-target="#{{ $collapseId }}"
                     role="button"
                     aria-expanded="false"
                     aria-controls="{{ $collapseId }}">

                    {{-- ÜST SATIR --}}
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="fw-semibold">{{ $order->code }}</span>
                                <small class="text-muted">— {{ $whenHuman }}</small>
                            </div>

                            <div class="text-muted booking-meta">
                                @foreach($types as $t)
                                    @php
                                        $label = match ($t) {
                                            'hotel', 'hotel_room' => 'Konaklama',
                                            'transfer' => 'Transfer',
                                            'villa' => 'Villa',
                                            'tour', 'excursion' => 'Günlük Tur',
                                            default => strtoupper((string) $t),
                                        };
                                    @endphp
                                    <span class="badge bg-secondary-subtle text-secondary align-middle my-1 fw-medium">{{ $label }}</span>
                                @endforeach
                            </div>
                        </div>

                        <div class="col-lg-auto col-12 align-self-center text-lg-end">
                            <span class="badge {{ $statusClass }} align-middle">{{ $statusLabel }}</span>
                            <br class="d-lg-block d-none">
                            <span class="align-middle">{{ $totalText }}</span>
                        </div>
                    </div>

                    {{-- ALT DETAY (AÇILAN ALAN) --}}
                    <div id="{{ $collapseId }}" class="collapse">
                        <hr>

                        {{-- ITEMS --}}
                        @foreach($items as $it)
                            @php
                                $t = $it['type'] ?? null;

                                $titleBadge = match ($t) {
                                    'hotel', 'hotel_room' => 'Konaklama',
                                    'transfer' => 'Transfer',
                                    'villa' => 'Villa',
                                    'tour', 'excursion' => 'Günlük Tur',
                                    default => null,
                                };

                                $img = $it['image'] ?? null;
                                $imageObj = \App\Support\Helpers\ImageHelper::normalize($img);
                                $pax = $it['pax'] ?? null;
                            @endphp

                            <div class="row">
                                <div class="col-lg-auto col-12 mb-lg-0 mb-3 position-relative">
                                    @if($titleBadge)
                                        <span class="badge bg-secondary-subtle text-secondary position-absolute ms-1 mt-1 fw-medium">{{ $titleBadge }}</span>
                                    @endif

                                    <x-responsive-image
                                        :image="$imageObj"
                                        preset="listing-card"
                                        class="booking-thumb"
                                    />
                                </div>

                                <div class="col">
                                    <dl class="row mb-0">
                                        @if($t === 'hotel' || $t === 'hotel_room')
                                            <dt class="col-lg-4">Otel</dt>
                                            <dd class="col-lg-8">{{ $it['hotel_name'] ?? '-' }}</dd>

                                            <dt class="col-lg-4">Oda</dt>
                                            <dd class="col-lg-8">{{ $it['room_name'] ?? '-' }}</dd>

                                            <dt class="col-lg-4">Konaklama Tipi</dt>
                                            <dd class="col-lg-8">{{ $it['board_type'] ?? '-' }}</dd>

                                            <dt class="col-lg-4">Tarihler</dt>
                                            <dd class="col-lg-8">{{ $it['checkin'] ?? '-' }} → {{ $it['checkout'] ?? '-' }}</dd>

                                            <dt class="col-lg-4">Misafirler</dt>
                                            <dd class="col-lg-8">{{ $pax ?? '-' }}</dd>

                                            <dt class="col-lg-4">Ücret</dt>
                                            <dd class="col-lg-8">{{ $it['paid'] ?? '-' }}</dd>
                                        @elseif($t === 'transfer')
                                            <dt class="col-lg-4">Rota</dt>
                                            <dd class="col-lg-8">{{ $it['route'] ?? '-' }}</dd>

                                            <dt class="col-lg-4">Araç</dt>
                                            <dd class="col-lg-8">{{ $it['vehicle'] ?? '-' }}</dd>

                                            <dt class="col-lg-4">Geliş</dt>
                                            <dd class="col-lg-8">
                                                {{ $it['departure_date'] ?? '-' }}
                                                @if(!empty($it['departure_flight']))
                                                    — {{ $it['departure_flight'] }}
                                                @endif
                                            </dd>

                                            <dt class="col-lg-4">Dönüş</dt>
                                            <dd class="col-lg-8">
                                                {{ $it['return_date'] ?? '-' }}
                                                @if(!empty($it['return_flight']))
                                                    — {{ $it['return_flight'] }}
                                                @endif
                                            </dd>

                                            <dt class="col-lg-4">Yolcular</dt>
                                            <dd class="col-lg-8">{{ $pax ?? '-' }}</dd>

                                            <dt class="col-lg-4">Ücret</dt>
                                            <dd class="col-lg-8">{{ $it['paid'] ?? '-' }}</dd>
                                        @elseif($t === 'villa')
                                            <dt class="col-lg-4">Villa</dt>
                                            <dd class="col-lg-8">{{ $it['villa_name'] ?? '-' }}</dd>

                                            <dt class="col-lg-4">Tarihler</dt>
                                            <dd class="col-lg-8">{{ $it['checkin'] ?? '-' }} → {{ $it['checkout'] ?? '-' }}</dd>

                                            <dt class="col-lg-4">Misafirler</dt>
                                            <dd class="col-lg-8">{{ $pax ?? '-' }}</dd>

                                            <dt class="col-lg-4">Ön ödeme</dt>
                                            <dd class="col-lg-8">{{ $it['paid'] ?? '-' }}</dd>

                                            <dt class="col-lg-4">Toplam Ücret</dt>
                                            <dd class="col-lg-8">
                                                {{ $it['total'] ?? '-' }}
                                                @if(!empty($it['remaining']))
                                                    (Kalan {{ $it['remaining'] }})
                                                @endif
                                            </dd>
                                        @elseif($t === 'tour' || $t === 'excursion')
                                            <dt class="col-lg-4">Tur</dt>
                                            <dd class="col-lg-8">{{ $it['tour_name'] ?? '-' }}</dd>

                                            <dt class="col-lg-4">Tarih</dt>
                                            <dd class="col-lg-8">{{ $it['date'] ?? '-' }}</dd>

                                            <dt class="col-lg-4">Misafirler</dt>
                                            <dd class="col-lg-8">{{ $pax ?? '-' }}</dd>

                                            <dt class="col-lg-4">Ücret</dt>
                                            <dd class="col-lg-8">{{ $it['paid'] ?? '-' }}</dd>
                                        @else
                                            <dt class="col-lg-4">Ücret</dt>
                                            <dd class="col-lg-8">{{ $it['paid'] ?? '-' }}</dd>
                                        @endif
                                    </dl>
                                </div>

                                <div class="col-12">
                                    <hr>
                                </div>
                            </div>
                        @endforeach

                        {{-- İNDİRİMLER --}}
                        @if($discounts->isNotEmpty())
                            <h6 class="mb-2 mt-3">Uygulanmış İndirimler</h6>
                            <div class="col-12">
                                @foreach($discounts as $d)
                                    <div class="bg-success-subtle rounded mb-2 p-2">
                                        <dl class="row mb-0">
                                            <dd class="col-lg-8 mb-0">
                                                @if(!empty($d['badge']))
                                                    <span class="badge bg-success fw-medium me-1">{{ $d['badge'] }}</span>
                                                @endif
                                                <span>{{ $d['label'] ?? '-' }}</span>
                                            </dd>
                                            <dt class="col-lg-4 mb-0">-{{ $d['amount'] ?? '-' }}</dt>
                                        </dl>
                                    </div>
                                @endforeach
                            </div>
                            <hr>
                        @endif

                        {{-- GERİ ÖDEMELER --}}
                        @if($refundRows->isNotEmpty())
                            <h6 class="mb-2 mt-3">Geri Ödemeler</h6>
                            <div class="col-12">
                                @foreach($refundRows as $r)
                                    <div class="bg-info-subtle rounded mb-2 p-2">
                                        <dl class="row mb-0">
                                            <dd class="col-lg-8 mb-0">
                                                {{ $r['reason'] ?? 'Geri ödeme' }}
                                            </dd>
                                            <dt class="col-lg-4 mb-0">
                                                +{{ $r['amount'] ?? '-' }}
                                                @if(!empty($r['time']))
                                                    <small class="text-muted fw-normal">({{ $r['time'] }})</small>
                                                @endif
                                            </dt>
                                        </dl>
                                    </div>
                                @endforeach
                                <p class="text-muted d-flex align-items-center mb-0">
                                    <i class="fi fi-rr-info me-1"></i>
                                    Bankanıza bağlı olarak 1-7 iş günü içinde tutar kartınıza yansıyabilir.
                                </p>
                            </div>
                            <hr>
                        @endif

                        {{-- AKSİYONLAR (şimdilik sadece UI) --}}
                        <div class="col-12 d-flex align-items-start justify-content-between gap-2">
                            <a href="#" class="btn btn-outline-secondary btn-sm" data-order-id="{{ $order->id }}">İptal et</a>
                            @php
                                $hasTicket = (bool) ($order->has_ticket ?? false);
                                $ticketId  = $order->ticket_id ?? null;

                                $supportHref = $hasTicket && $ticketId
                                    ? localized_route('account.tickets.show', ['ticket' => $ticketId])
                                    : localized_route('account.tickets.create', ['order_id' => $order->id]);
                            @endphp

                            <a href="{{ $supportHref }}"
                               class="btn {{ $hasTicket ? 'btn-outline-secondary' : 'btn-outline-primary' }} btn-sm"
                               @if($hasTicket) aria-label="Mevcut destek talebine git" @else aria-label="Bu sipariş için destek talebi oluştur" @endif>
                                {{ $hasTicket ? 'Mevcut Destek Talebi' : 'Destek talebi oluştur' }}
                            </a>

                        </div>
                    </div>
                </div>
            </div>
        @endforeach
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
                const wanted = statusFilter.value;

                cards.forEach(c => {
                    const st = (c.getAttribute('data-status') || '').toLowerCase();
                    const show = (wanted === 'all') || (st === wanted);
                    c.classList.toggle('d-none', !show);
                });
            }

            function applySort(cards) {
                list.querySelectorAll('.collapse.show').forEach(el => {
                    const acc = bootstrap.Collapse.getOrCreateInstance(el);
                    acc.hide();
                });

                const mode = sortSelect.value;
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

            refresh();
        });
    </script>

@endsection
