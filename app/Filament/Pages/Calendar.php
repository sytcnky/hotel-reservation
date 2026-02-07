<?php

namespace App\Filament\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Hotel;
use App\Models\OrderItem;
use App\Models\Room;
use App\Models\Villa;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;


class Calendar extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.calendar';

    public function getMaxContentWidth(): Width
    {
        return Width::Full;
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.order_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.calender.singular');
    }

    /**
     * Livewire filter state (Filament Select'ler buraya bağlanır)
     */
    public array $filters = [
        'type'     => 'all',  // all|hotel|villa|transfer|tour
        'hotel_id' => null,   // type=hotel
        'room_id'   => null,
        'villa_id' => null,   // type=villa
    ];

    /**
     * Ürün tipine göre renkler
     */
    private function colorForFamily(string $familyKey): array
    {
        return match ($familyKey) {
            'hotel'    => ['backgroundColor' => '#26547c', 'borderColor' => '#26547c', 'textColor' => '#ffffff'],
            'villa'    => ['backgroundColor' => '#7c3aed', 'borderColor' => '#7c3aed', 'textColor' => '#ffffff'],
            'transfer' => ['backgroundColor' => '#f59e0b', 'borderColor' => '#f59e0b', 'textColor' => '#ffffff'],
            'tour'     => ['backgroundColor' => '#16a34a', 'borderColor' => '#16a34a', 'textColor' => '#ffffff'],
            default    => ['backgroundColor' => '#6b7280', 'borderColor' => '#6b7280', 'textColor' => '#ffffff'],
        };
    }

    /**
     * name cast'ı array => UI locale'e göre label seç.
     */
    private function localizedName($name): string
    {
        $ui = (string) app()->getLocale();
        $ui = in_array($ui, ['tr', 'en'], true) ? $ui : 'en';

        if (is_array($name)) {
            $v = trim((string) ($name[$ui] ?? $name['tr'] ?? $name['en'] ?? ''));
            return $v !== '' ? $v : '-';
        }

        $v = trim((string) $name);
        return $v !== '' ? $v : '-';
    }

    /**
     * Hotel options (id => localized name)
     */
    private function hotelOptions(): array
    {
        $rows = Hotel::query()->select(['id', 'name'])->whereNull('deleted_at')->get();

        $map = [];
        foreach ($rows as $r) {
            $map[(string) $r->id] = $this->localizedName($r->name);
        }

        // PHP sort by label (locale aware)
        asort($map, SORT_NATURAL | SORT_FLAG_CASE);

        return $map;
    }

    /**
     * Villa options (id => localized name)
     */
    private function villaOptions(): array
    {
        $rows = Villa::query()->select(['id', 'name'])->whereNull('deleted_at')->get();

        $map = [];
        foreach ($rows as $r) {
            $map[(string) $r->id] = $this->localizedName($r->name);
        }

        asort($map, SORT_NATURAL | SORT_FLAG_CASE);

        return $map;
    }

    /**
     * Villa options (id => localized name)
     */
    private function roomOptions(): array
    {
        $hotelId = (int) ($this->filters['hotel_id'] ?? 0);
        if ($hotelId <= 0) {
            return [];
        }

        $rows = Room::query()
            ->where('hotel_id', $hotelId)   // Room tablosunda ilişki kolonu buysa
            ->whereNull('deleted_at')
            ->select(['id', 'name'])
            ->get();

        $map = [];
        foreach ($rows as $r) {
            $map[(string) $r->id] = $this->localizedName($r->name);
        }

        asort($map, SORT_NATURAL | SORT_FLAG_CASE);

        return $map;
    }


    /**
     * Filtre şeması (Filament Select)
     */
    public function filtersSchema(Schema $schema): Schema
    {
        return $schema
            ->statePath('filters')
            ->components([
                Grid::make(12)
                    ->components([
                        Select::make('type')
                            ->label(__('admin.calender.product_type'))
                            ->hiddenLabel()
                            ->options([
                                'all'      => __('admin.calender.all_type'),
                                'hotel'    => __('admin.calender.product_type_hotel'),
                                'villa'    => __('admin.calender.product_type_villa'),
                                'transfer' => __('admin.calender.product_type_transfer'),
                                'tour'     => __('admin.calender.product_type_tour'),
                            ])
                            ->native(false)
                            ->live()
                            ->columnSpan(3),

                        Select::make('hotel_id')
                            ->label(__('admin.calender.product_type_hotel'))
                            ->hiddenLabel()
                            ->options(fn () => $this->hotelOptions())
                            ->native(false)
                            ->searchable()
                            ->live()
                            ->visible(fn () => (($this->filters['type'] ?? 'all') === 'hotel'))
                            ->columnSpan(3),

                        Select::make('villa_id')
                            ->label(__('admin.calender.product_type_villa'))
                            ->hiddenLabel()
                            ->options(fn () => $this->villaOptions())
                            ->native(false)
                            ->searchable()
                            ->live()
                            ->visible(fn () => (($this->filters['type'] ?? 'all') === 'villa'))
                            ->columnSpan(3),

                        Select::make('room_id')
                            ->label(__('admin.calender.product_type_room'))
                            ->hiddenLabel()
                            ->options(fn () => $this->roomOptions())
                            ->native(false)
                            ->searchable()
                            ->live()
                            ->visible(fn () =>
                                ($this->filters['type'] ?? 'all') === 'hotel'
                                && ! empty($this->filters['hotel_id'])
                            )
                            ->columnSpan(3),
                    ]),
            ]);
    }

    /**
     * type değişince alt select reset + calendar refetch
     */
    public function updatedFiltersType($value): void
    {
        $value = is_string($value) ? strtolower(trim($value)) : 'all';
        $value = in_array($value, ['all', 'hotel', 'villa', 'transfer', 'tour'], true) ? $value : 'all';

        if ($value !== 'hotel') {
            $this->filters['hotel_id'] = null;
        }

        if ($value !== 'villa') {
            $this->filters['villa_id'] = null;
        }

        $this->dispatch('calendar:refetch');
    }

    public function updatedFiltersHotelId(): void
    {
        $this->filters['room_id'] = null;
        $this->dispatch('calendar:refetch');
    }

    public function updatedFiltersVillaId(): void
    {
        $this->dispatch('calendar:refetch');
    }

    public function updatedFiltersRoomId(): void
    {
        $this->dispatch('calendar:refetch');
    }

    /**
     * FullCalendar -> Livewire events kaynağı.
     *
     * @param  string  $start  YYYY-MM-DD (inclusive)
     * @param  string  $end    YYYY-MM-DD (exclusive)
     * @param  array<string, mixed>  $filters
     * @return array<int, array<string, mixed>>
     */
    public function getCalendarEvents(string $start, string $end, array $filters = []): array
    {
        $start = trim($start);
        $end   = trim($end);

        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
            return [];
        }

        $type = strtolower(trim((string) ($filters['type'] ?? 'all')));
        $type = in_array($type, ['all', 'hotel', 'villa', 'transfer', 'tour'], true) ? $type : 'all';

        $hotelId = isset($filters['hotel_id']) ? (int) $filters['hotel_id'] : 0;
        $villaId = isset($filters['villa_id']) ? (int) $filters['villa_id'] : 0;
        $roomId = isset($filters['room_id']) ? (int) $filters['room_id'] : 0;

        // type -> DB product_type list
        $dbTypes = [];
        if ($type !== 'all') {
            if ($type === 'hotel') {
                $dbTypes = ['hotel', 'hotel_room'];
            } elseif ($type === 'tour') {
                $dbTypes = ['tour', 'excursion'];
            } else {
                $dbTypes = [$type];
            }
        }

        $q = OrderItem::query()
            ->select([
                'order_items.id',
                'order_items.order_id',
                'order_items.product_type',
                'order_items.title',
                'order_items.snapshot',

                'orders.code as order_code',
                'orders.status as order_status',
                'orders.customer_name as customer_name',
            ])
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereNull('order_items.deleted_at')
            ->whereNull('orders.deleted_at')
            ->where('orders.status', \App\Models\Order::STATUS_CONFIRMED);

        if (! empty($dbTypes)) {
            $q->whereIn('order_items.product_type', $dbTypes);
        }

        // 2. select: hotel/villa seçildiyse snapshot üzerinden filtrele
        if ($type === 'hotel' && $hotelId > 0) {
            $q->whereRaw("(order_items.snapshot->>'hotel_id') = ?", [(string) $hotelId]);
        }

        if ($type === 'villa' && $villaId > 0) {
            $q->whereRaw("(order_items.snapshot->>'villa_id') = ?", [(string) $villaId]);
        }

        if ($type === 'hotel' && $roomId > 0) {
            $q->whereRaw("(order_items.snapshot->>'room_id') = ?", [(string) $roomId]);
        }

        // Date range filtre (PostgreSQL JSONB) - end exclusive
        $q->where(function ($w) use ($start, $end) {
            $w->orWhere(function ($x) use ($start, $end) {
                $x->whereIn('order_items.product_type', ['hotel', 'hotel_room'])
                    ->whereRaw("(order_items.snapshot->>'checkin') < ?", [$end])
                    ->whereRaw("(order_items.snapshot->>'checkout') > ?", [$start]);
            });

            $w->orWhere(function ($x) use ($start, $end) {
                $x->where('order_items.product_type', 'villa')
                    ->whereRaw("(order_items.snapshot->>'checkin') < ?", [$end])
                    ->whereRaw("(order_items.snapshot->>'checkout') > ?", [$start]);
            });

            $w->orWhere(function ($x) use ($start, $end) {
                $x->whereIn('order_items.product_type', ['tour', 'excursion'])
                    ->whereRaw("(order_items.snapshot->>'date') >= ?", [$start])
                    ->whereRaw("(order_items.snapshot->>'date') < ?", [$end]);
            });

            $w->orWhere(function ($x) use ($start, $end) {
                $x->where('order_items.product_type', 'transfer')
                    ->where(function ($t) use ($start, $end) {
                        $t->where(function ($a) use ($start, $end) {
                            $a->whereRaw("(order_items.snapshot->>'departure_date') >= ?", [$start])
                                ->whereRaw("(order_items.snapshot->>'departure_date') < ?", [$end]);
                        })->orWhere(function ($b) use ($start, $end) {
                            $b->whereRaw("(order_items.snapshot->>'return_date') >= ?", [$start])
                                ->whereRaw("(order_items.snapshot->>'return_date') < ?", [$end]);
                        });
                    });
            });
        });

        $rows = $q->orderByDesc('orders.id')->orderByDesc('order_items.id')->get();

        $events = [];

        foreach ($rows as $row) {
            $snapshot = is_array($row->snapshot) ? $row->snapshot : (array) $row->snapshot;

            $pt = strtolower((string) $row->product_type);

            $orderId   = (int) $row->order_id;
            $orderCode = trim((string) ($row->order_code ?? ''));
            $status    = (string) ($row->order_status ?? '');
            $customerName = trim((string) ($row->customer_name ?? ''));

            $orderUrl = $orderId > 0
                ? OrderResource::getUrl('edit', ['record' => $orderId])
                : null;

            $familyKey = match ($pt) {
                'hotel', 'hotel_room' => 'hotel',
                'villa'               => 'villa',
                'tour', 'excursion'   => 'tour',
                'transfer'            => 'transfer',
                default               => $pt,
            };

            $base  = $customerName !== '' ? $customerName : ($orderCode !== '' ? $orderCode : '-');
            $familyLabel = match ($familyKey) {
                'hotel'    => __('admin.calender.product_type_hotel'),
                'villa'    => __('admin.calender.product_type_villa'),
                'tour'     => __('admin.calender.product_type_tour'),
                'transfer' => __('admin.calender.product_type_transfer'),
                default    => strtoupper($familyKey),
            };

            $label = $base . ' (' . $familyLabel . ')';

            // HOTEL / VILLA (range)
            if ($pt === 'hotel' || $pt === 'hotel_room' || $pt === 'villa') {
                $checkin  = isset($snapshot['checkin']) ? trim((string) $snapshot['checkin']) : '';
                $checkout = isset($snapshot['checkout']) ? trim((string) $snapshot['checkout']) : '';
                $colors   = $this->colorForFamily($familyKey);

                if ($checkin !== '' && $checkout !== '') {
                    $events[] = [
                        'id'     => 'oi_' . $row->id,
                        'title'  => $label,
                        'start'  => $checkin,
                        'end'    => $checkout,
                        'allDay' => true,
                        ...$colors,
                        'extendedProps' => [
                            'type'      => $pt === 'villa' ? 'villa' : 'hotel',
                            'status'    => $status,
                            'orderId'   => $orderId,
                            'orderCode' => $orderCode,
                            'orderUrl'  => $orderUrl,
                            'itemId'    => (int) $row->id,
                        ],
                    ];
                }

                continue;
            }


            // TOUR / EXCURSION (day)
            if ($pt === 'tour' || $pt === 'excursion') {
                $date   = isset($snapshot['date']) ? trim((string) $snapshot['date']) : '';
                $colors = $this->colorForFamily($familyKey);

                if ($date !== '') {
                    $events[] = [
                        'id'      => 'oi_' . $row->id,
                        'title'   => $label,
                        'start'   => $date,
                        'allDay'  => true,
                        'display' => 'list-item',
                        ...$colors,
                        'extendedProps' => [
                            'type'      => 'tour',
                            'status'    => $status,
                            'orderId'   => $orderId,
                            'orderCode' => $orderCode,
                            'orderUrl'  => $orderUrl,
                            'itemId'    => (int) $row->id,
                        ],
                    ];
                }

                continue;
            }

            // TRANSFER (timed if pickup_time present) -> 2 ayrı event
            if ($pt === 'transfer') {
                $depDate = isset($snapshot['departure_date']) ? trim((string) $snapshot['departure_date']) : '';
                $retDate = isset($snapshot['return_date']) ? trim((string) $snapshot['return_date']) : '';

                $depTime = isset($snapshot['pickup_time_outbound']) ? trim((string) $snapshot['pickup_time_outbound']) : '';
                $retTime = isset($snapshot['pickup_time_return']) ? trim((string) $snapshot['pickup_time_return']) : '';

                $colors = $this->colorForFamily($familyKey);

                if ($depDate !== '') {
                    $isTimed = $depTime !== '' && preg_match('/^\d{2}:\d{2}$/', $depTime);

                    $events[] = [
                        'id'      => 'oi_' . $row->id . '_dep',
                        'title'   => $label,
                        'start'   => $isTimed ? ($depDate . 'T' . $depTime) : $depDate,
                        'allDay'  => $isTimed ? false : true,
                        'display' => 'list-item',
                        ...$colors,
                        'extendedProps' => [
                            'type'      => 'transfer',
                            'subtype'   => 'departure',
                            'status'    => $status,
                            'orderId'   => $orderId,
                            'orderCode' => $orderCode,
                            'orderUrl'  => $orderUrl,
                            'itemId'    => (int) $row->id,
                        ],
                    ];
                }

                if ($retDate !== '') {
                    $isTimed = $retTime !== '' && preg_match('/^\d{2}:\d{2}$/', $retTime);

                    $events[] = [
                        'id'      => 'oi_' . $row->id . '_ret',
                        'title'   => $label,
                        'start'   => $isTimed ? ($retDate . 'T' . $retTime) : $retDate,
                        'allDay'  => $isTimed ? false : true,
                        'display' => 'list-item',
                        ...$colors,
                        'extendedProps' => [
                            'type'      => 'transfer',
                            'subtype'   => 'return',
                            'status'    => $status,
                            'orderId'   => $orderId,
                            'orderCode' => $orderCode,
                            'orderUrl'  => $orderUrl,
                            'itemId'    => (int) $row->id,
                        ],
                    ];
                }

                continue;
            }
        }

        return $events;
    }
}
