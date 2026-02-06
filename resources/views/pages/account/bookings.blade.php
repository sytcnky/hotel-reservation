@extends('layouts.account')

@section('account_content')

    @php
        /** @var \Illuminate\Support\Collection|\App\Models\Order[] $orders */
        $orders = $orders ?? collect();

        $statusList = \App\Models\Order::statusList();

        $statusFilterOptions = [
            'all' => t('account.bookings.filter.status_all'),
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
                    <option value="date_desc">{{ t('account.bookings.sort.date_desc') }}</option>
                    <option value="date_asc">{{ t('account.bookings.sort.date_asc') }}</option>
                    <option value="price_desc">{{ t('account.bookings.sort.price_desc') }}</option>
                    <option value="price_asc">{{ t('account.bookings.sort.price_asc') }}</option>
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
        .booking-meta { font-size: .925rem }
    </style>

    <div id="bookingList">

        @if($orders->isEmpty())
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">{{ t('account.bookings.empty') }}</div>
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

                $whenTs = optional($order->created_at)?->getTimestampMs() ?? 0;
                $whenHuman = \App\Support\Date\DatePresenter::humanDateTime(
                    dt: $order->created_at,
                    pattern: 'd F Y H:i'
                );

                $collapseId = 'booking-' . $order->id;

                $discounts = collect($order->discounts_for_infolist ?? []);

                if ($order->relationLoaded('refundAttempts')) {
                    $refundRows = collect($order->refundAttempts ?? [])->map(function ($r) use ($currency) {
                        $amountText = \App\Support\Currency\CurrencyPresenter::format($r->amount ?? null, $currency);

                        return [
                            'reason' => $r->reason ?: null,
                            'amount' => $amountText,
                            'time' => \App\Support\Date\DatePresenter::humanDateTime($r->created_at),
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
                 data-when="{{ $whenTs }}"
                 data-price="{{ (int) round((float) ($order->total_amount ?? 0)) }}"
                 data-status="{{ $status }}">

                <div class="card-body align-items-center g-3 p-0">
                    <div class="booking-toggle p-3"
                         role="button"
                         data-bs-toggle="collapse"
                         data-bs-target="#{{ $collapseId }}"
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
                                            // mevcut nav.* anahtarlarına bağla (kullanıcı talebi)
                                            $label = match ($t) {
                                                'hotel', 'hotel_room' => t('nav.hotels'),
                                                'transfer' => t('nav.transfers'),
                                                'villa' => t('nav.villas'),
                                                'tour', 'excursion' => t('nav.excursions'),
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
                    </div>

                    <div class="px-3">
                        {{-- ALT DETAY (AÇILAN ALAN) --}}
                        <div id="{{ $collapseId }}" class="collapse">
                            <hr>

                            {{-- ITEMS --}}
                            @foreach($items as $it)
                                @php
                                    $t = $it['type'] ?? null;

                                    // mevcut nav.* anahtarlarına bağla (kullanıcı talebi)
                                    $titleBadge = match ($t) {
                                        'hotel', 'hotel_room' => t('nav.hotels'),
                                        'transfer' => t('nav.transfers'),
                                        'villa' => t('nav.villas'),
                                        'tour', 'excursion' => t('nav.excursions'),
                                        default => null,
                                    };

                                    $imageObj = $it['image'] ?? null;
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
                                                <dt class="col-lg-4">{{ t('account.bookings.fields.hotel') }}</dt>
                                                <dd class="col-lg-8">{{ $it['hotel_name'] ?? t('account.bookings.value.empty') }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.room') }}</dt>
                                                <dd class="col-lg-8">{{ $it['room_name'] ?? t('account.bookings.value.empty') }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.board_type') }}</dt>
                                                <dd class="col-lg-8">{{ $it['board_type'] ?? t('account.bookings.value.empty') }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.dates') }}</dt>
                                                <dd class="col-lg-8">
                                                    {{ \App\Support\Date\DatePresenter::human($it['checkin'] ?? null) }}
                                                    →
                                                    {{ \App\Support\Date\DatePresenter::human($it['checkout'] ?? null) }}
                                                </dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.guests') }}</dt>
                                                <dd class="col-lg-8">{{ $pax ?? t('account.bookings.value.empty') }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.fee') }}</dt>
                                                <dd class="col-lg-8">{{ $it['paid'] ?? t('account.bookings.value.empty') }}</dd>

                                            @elseif($t === 'transfer')
                                                <dt class="col-lg-4">{{ t('account.bookings.fields.route') }}</dt>
                                                <dd class="col-lg-8">{{ $it['route'] ?? t('account.bookings.value.empty') }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.vehicle') }}</dt>
                                                <dd class="col-lg-8">{{ $it['vehicle'] ?? t('account.bookings.value.empty') }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.departure') }}</dt>
                                                <dd class="col-lg-8">
                                                    {{ \App\Support\Date\DatePresenter::human($it['departure_date'] ?? null) }}
                                                    @if(!empty($it['departure_flight']))
                                                        — {{ $it['departure_flight'] }}
                                                    @endif
                                                </dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.return') }}</dt>
                                                <dd class="col-lg-8">
                                                    {{ \App\Support\Date\DatePresenter::human($it['return_date'] ?? null) }}
                                                    @if(!empty($it['return_flight']))
                                                        — {{ $it['return_flight'] }}
                                                    @endif
                                                </dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.passengers') }}</dt>
                                                <dd class="col-lg-8">{{ $pax ?? t('account.bookings.value.empty') }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.fee') }}</dt>
                                                <dd class="col-lg-8">{{ $it['paid'] ?? t('account.bookings.value.empty') }}</dd>

                                            @elseif($t === 'villa')
                                                <dt class="col-lg-4">{{ t('account.bookings.fields.villa') }}</dt>
                                                <dd class="col-lg-8">{{ $it['villa_name'] ?? t('account.bookings.value.empty') }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.dates') }}</dt>
                                                <dd class="col-lg-8">
                                                    {{ \App\Support\Date\DatePresenter::human($it['checkin'] ?? null) }} → {{ \App\Support\Date\DatePresenter::human($it['checkout'] ?? null) }}
                                                </dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.guests') }}</dt>
                                                <dd class="col-lg-8">{{ $pax ?? t('account.bookings.value.empty') }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.prepayment') }}</dt>
                                                <dd class="col-lg-8">{{ $it['paid'] ?? t('account.bookings.value.empty') }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.total_fee') }}</dt>
                                                <dd class="col-lg-8">
                                                    {{ $it['total'] ?? t('account.bookings.value.empty') }}
                                                    @if(!empty($it['remaining']))
                                                        ({{ t('account.bookings.remaining', ['amount' => $it['remaining']]) }})
                                                    @endif
                                                </dd>

                                            @elseif($t === 'tour' || $t === 'excursion')
                                                <dt class="col-lg-4">{{ t('account.bookings.fields.tour') }}</dt>
                                                <dd class="col-lg-8">{{ $it['tour_name'] ?? t('account.bookings.value.empty') }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.date') }}</dt>
                                                <dd class="col-lg-8">{{ \App\Support\Date\DatePresenter::human($it['date'] ?? null) }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.guests') }}</dt>
                                                <dd class="col-lg-8">{{ $pax ?? t('account.bookings.value.empty') }}</dd>

                                                <dt class="col-lg-4">{{ t('account.bookings.fields.fee') }}</dt>
                                                <dd class="col-lg-8">{{ $it['paid'] ?? t('account.bookings.value.empty') }}</dd>

                                            @else
                                                <dt class="col-lg-4">{{ t('account.bookings.fields.fee') }}</dt>
                                                <dd class="col-lg-8">{{ $it['paid'] ?? t('account.bookings.value.empty') }}</dd>
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
                                <h6 class="mb-2 mt-3">{{ t('account.bookings.discounts.title') }}</h6>
                                <div class="col-12">
                                    @foreach($discounts as $d)
                                        <div class="bg-success-subtle rounded mb-2 p-2">
                                            <dl class="row mb-0">
                                                <dd class="col-lg-8 mb-0">
                                                    @if(!empty($d['badge']))
                                                        <span class="badge bg-success fw-medium me-1">{{ $d['badge'] }}</span>
                                                    @endif
                                                    <span>{{ $d['label'] ?? t('account.bookings.value.empty') }}</span>
                                                </dd>
                                                <dt class="col-lg-4 mb-0">-{{ $d['amount'] ?? t('account.bookings.value.empty') }}</dt>
                                            </dl>
                                        </div>
                                    @endforeach
                                </div>
                                <hr>
                            @endif

                            {{-- GERİ ÖDEMELER --}}
                            @if($refundRows->isNotEmpty())
                                <h6 class="mb-2 mt-3">{{ t('account.bookings.refunds.title') }}</h6>
                                <div class="col-12">
                                    @foreach($refundRows as $r)
                                        <div class="bg-info-subtle rounded mb-2 p-2">
                                            <dl class="row mb-0">
                                                <dd class="col-lg-8 mb-0">
                                                    {{ $r['reason'] ?? t('account.bookings.refunds.default_reason') }}
                                                </dd>
                                                <dt class="col-lg-4 mb-0">
                                                    +{{ $r['amount'] ?? t('account.bookings.value.empty') }}
                                                    @if(!empty($r['time']))
                                                        <small class="text-muted fw-normal">({{ $r['time'] }})</small>
                                                    @endif
                                                </dt>
                                            </dl>
                                        </div>
                                    @endforeach
                                    <p class="text-muted d-flex align-items-center mb-0">
                                        <i class="fi fi-rr-info me-1"></i>
                                        {{ t('account.bookings.refunds.bank_note') }}
                                    </p>
                                </div>
                                <hr>
                            @endif

                            {{-- AKSİYONLAR (şimdilik sadece UI) --}}
                            <div class="col-12">
                                <div class="d-flex align-items-start justify-content-between gap-2 py-3">
                                    <button type="button"
                                            class="btn btn-outline-secondary btn-sm"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#{{ $collapseId }}">
                                        {{ t('account.bookings.actions.hide_details') }}
                                    </button>

                                    @php
                                        $hasTicket = (bool) ($order->has_ticket ?? false);
                                        $ticketId  = $order->ticket_id ?? null;

                                        $supportHref = $hasTicket && $ticketId
                                            ? localized_route('account.tickets.show', ['ticket' => $ticketId])
                                            : localized_route('account.tickets.create', [
                                                'order_id' => $order->id,
                                                'category_id' => $orderCategoryId,
                                            ]);

                                        $supportAria = $hasTicket
                                            ? t('account.bookings.aria.go_to_ticket')
                                            : t('account.bookings.aria.create_ticket');

                                        $supportText = $hasTicket
                                            ? t('account.bookings.actions.support_existing')
                                            : t('account.bookings.actions.support_create');
                                    @endphp

                                    <a href="{{ $supportHref }}"
                                       class="btn {{ $hasTicket ? 'btn-outline-secondary' : 'btn-outline-primary' }} btn-sm"
                                       aria-label="{{ $supportAria }}">
                                        {{ $supportText }}
                                    </a>
                                </div>
                            </div>
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
                const v = parseInt(card.getAttribute('data-when') || '0', 10);
                return isNaN(v) ? 0 : v;
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
