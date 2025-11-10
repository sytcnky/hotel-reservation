<?php

namespace App\Filament\Resources\Rooms\Schemas;

use App\Models\BedType;
use App\Models\Currency;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomFacility;
use App\Models\ViewType;
use App\Models\BoardType;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;

class RoomForm
{
    public static function configure(Schema $schema): Schema
    {
        $base = config('app.locale', 'tr');
        $locales = config('app.supported_locales', [$base]);
        $uiLocale = app()->getLocale();

        $labelSql = "COALESCE(NULLIF(name->>?, ''), NULLIF(name->>?, ''), '-')";

        // Aktif para birimleri
        $currencies = Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        // BoardType seçenekleri (UI için)
        $boardOptions = BoardType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(function (BoardType $b) use ($uiLocale, $base) {
                $name = $b->name[$uiLocale] ?? ($b->name[$base] ?? (array_values($b->name ?? [])[0] ?? ''));
                return [$b->getKey() => (string) $name];
            })
            ->all();

        // Hafta günleri (1=Mon ... 7=Sun)
        $weekdayOptions = [
            1 => __('admin.weekdays.mon') ?? 'Pzt',
            2 => __('admin.weekdays.tue') ?? 'Sal',
            3 => __('admin.weekdays.wed') ?? 'Çar',
            4 => __('admin.weekdays.thu') ?? 'Per',
            5 => __('admin.weekdays.fri') ?? 'Cum',
            6 => __('admin.weekdays.sat') ?? 'Cmt',
            7 => __('admin.weekdays.sun') ?? 'Paz',
        ];

        $currencyTabs = $currencies->map(function (Currency $c) use ($boardOptions, $weekdayOptions) {
            $title = trim($c->code . ' – ' . ($c->name_l ?: ''));

            $rulesRepeater = Repeater::make('rates_'.$c->id)
                ->hiddenLabel()
                ->defaultItems(0)
                ->collapsible()
                ->collapsed()
                ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                ->addActionLabel(__('admin.rooms.form.prices.add_price_rule'))
                ->schema([
                    Grid::make()->columns(12)->schema([

                        Hidden::make('id'),
                        Hidden::make('currency_id')->default($c->id),

                        TextInput::make('label')
                            ->label(__('admin.rooms.form.prices.rule_name'))
                            ->columnSpan(9)
                            ->required()
                            ->live(onBlur: true),

                        TextInput::make('priority')
                            ->label(__('admin.rooms.form.prices.priority'))
                            ->numeric()
                            ->default(10)
                            ->columnSpan(3),

                        Section::make(__('admin.rooms.form.prices.dates'))
                            ->columns(12)
                            ->columnSpan(12)
                            ->schema([
                                DatePicker::make('date_start')
                                    ->label(__('admin.rooms.form.prices.date_start'))
                                    ->native(false)
                                    ->displayFormat('Y-m-d')
                                    ->columnSpan(6),
                                DatePicker::make('date_end')
                                    ->label(__('admin.rooms.form.prices.date_end'))
                                    ->native(false)
                                    ->displayFormat('Y-m-d')
                                    ->minDate(fn ($get) => $get('date_start'))
                                    ->columnSpan(6),

                                CheckboxList::make('weekdays')->label(__('admin.rooms.form.prices.days'))->options($weekdayOptions)->default([1,2,3,4,5,6,7])->columns(7)->columnSpan(12),
                            ])
                            ->secondary()
                            ->compact(),

                        Section::make(__('admin.rooms.form.prices.occupancy'))
                            ->columns(12)
                            ->columnSpan(6)
                            ->schema([
                                TextInput::make('occupancy_min')
                                    ->label(__('admin.rooms.form.prices.min'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->columnSpan(6),

                                TextInput::make('occupancy_max')
                                    ->label(__('admin.rooms.form.prices.max'))
                                    ->numeric()
                                    ->minValue(fn (Get $get) => (int) max(1, (int) $get('occupancy_min')))
                                    ->default(1)
                                    ->columnSpan(6),
                            ])
                            ->secondary()
                            ->compact(),

                        Section::make(__('admin.rooms.form.prices.los'))
                            ->columns(12)
                            ->columnSpan(6)
                            ->schema([
                                TextInput::make('los_min')
                                    ->label(__('admin.rooms.form.prices.min'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->columnSpan(6),
                                TextInput::make('los_max')
                                    ->label(__('admin.rooms.form.prices.max'))
                                    ->numeric()
                                    ->minValue(fn (Get $get) => (int) max(0, (int) $get('los_min')))
                                    ->columnSpan(6),
                            ])
                            ->secondary()
                            ->compact(),

                        Select::make('board_type_id')->label(__('admin.ent.board_type.singular'))
                            ->options($boardOptions)->native(false)->searchable()->preload()->columnSpan(6),

                        Radio::make('price_type')->label(__('admin.rooms.form.prices.price_type'))->options([
                            'room_per_night' => __('admin.rooms.form.prices.room_per_night'),
                            'person_per_night' => __('admin.rooms.form.prices.person_per_night'),
                        ])->inline()->default('room_per_night')->columnSpan(6),

                        TextInput::make('allotment')->label(__('admin.rooms.form.prices.allotment'))->numeric()->minValue(0)->columnSpan(6),
                        TextInput::make('amount')
                            ->label(fn () => __('admin.rooms.form.prices.price', [
                                'currency' => (string) $c->code,
                            ]))
                            ->numeric()
                            ->minValue(0)
                            ->required(false)
                            ->disabled(fn (Get $get) => (bool) $get('closed'))
                            ->columnSpan(6),

                        Toggle::make('closed')
                            ->label('Kapalı')
                            ->helperText(__('admin.rooms.form.prices.closed_helper'))
                            ->live()->columnSpan(4),
                        Toggle::make('cta')
                            ->label(__('admin.rooms.form.prices.cta'))
                            ->helperText(__('admin.rooms.form.prices.cta_helper'))
                            ->columnSpan(4),
                        Toggle::make('ctd')
                            ->label(__('admin.rooms.form.prices.ctd'))
                            ->helperText(__('admin.rooms.form.prices.ctd_helper'))
                            ->columnSpan(4),

                        Textarea::make('note')
                            ->label(__('admin.rooms.form.prices.note'))
                            ->rows(2)
                            ->columnSpan(12)
                    ]),

                ])
                ->afterStateHydrated(function ($component, $state, ?Room $record) use ($c) {
                    if (! $record?->exists) return;
                    $rows = $record->rateRules()
                        ->where('currency_id', $c->id)
                        ->orderBy('priority', 'desc')
                        ->orderBy('date_start')
                        ->get()
                        ->map(function (\App\Models\RoomRateRule $r) {
                            $days = [];
                            for ($i=1; $i<=7; $i++) if ($r->weekday_mask & (1 << ($i-1))) $days[] = $i;
                            return [
                                'id' => $r->id,
                                'label' => $r->label,
                                'currency_id' => $r->currency_id,
                                'board_type_id' => $r->board_type_id,
                                'price_type' => $r->price_type,
                                'date_start' => $r->date_start?->format('Y-m-d'),
                                'date_end' => $r->date_end?->format('Y-m-d'),
                                'weekdays' => $days,
                                'occupancy_min' => $r->occupancy_min,
                                'occupancy_max' => $r->occupancy_max,
                                'amount' => $r->amount,
                                'los_min' => $r->los_min,
                                'los_max' => $r->los_max,
                                'allotment' => $r->allotment,
                                'closed' => $r->closed,
                                'cta' => $r->cta,
                                'ctd' => $r->ctd,
                                'priority' => $r->priority,
                                'note' => $r->note,
                            ];
                        })->values()->all();

                    $component->state($rows);
                })
                ->saveRelationshipsUsing(function (Room $record, array $state) use ($c) {
                    $nullIfEmpty = fn($v) => ($v === '' || $v === null) ? null : $v;

                    $keepIds = [];
                    foreach ($state as $row) {
                        $start = $nullIfEmpty($row['date_start'] ?? null);
                        $end   = $nullIfEmpty($row['date_end'] ?? null);

                        $attrs = [
                            'room_id'       => $record->getKey(),
                            'label'         => $row['label'] ?? null,
                            'currency_id'   => $c->id,
                            'board_type_id' => $nullIfEmpty($row['board_type_id'] ?? null),

                            'price_type'    => $row['price_type'] ?? 'person_per_night',
                            'date_start'    => $start,
                            'date_end'      => $end,

                            'weekday_mask'  => \App\Models\RoomRateRule::weekdaysToMask($row['weekdays'] ?? []),
                            'occupancy_min' => max(1, (int)($row['occupancy_min'] ?? 1)),
                            'occupancy_max' => max(1, (int)($row['occupancy_max'] ?? 1)),

                            'amount'        => !empty($row['closed']) ? 0 : (float)($row['amount'] ?? 0),

                            'los_min'       => $nullIfEmpty($row['los_min'] ?? null),
                            'los_max'       => $nullIfEmpty($row['los_max'] ?? null),
                            'allotment'     => $nullIfEmpty($row['allotment'] ?? null),

                            'closed'        => (bool)($row['closed'] ?? false),
                            'cta'           => (bool)($row['cta'] ?? false),
                            'ctd'           => (bool)($row['ctd'] ?? false),

                            'priority'      => (int)($row['priority'] ?? 10),
                            'note'          => $nullIfEmpty($row['note'] ?? null),
                            'is_active'     => true,
                        ];

                        $id = $row['id'] ?? null;
                        if ($id) {
                            $record->rateRules()->whereKey($id)->update($attrs);
                            $keepIds[] = (int)$id;
                        } else {
                            $created = $record->rateRules()->create($attrs);
                            $keepIds[] = $created->getKey();
                        }
                    }

                    $record->rateRules()
                        ->where('currency_id', $c->id)
                        ->when(!empty($keepIds), fn($q) => $q->whereNotIn('id', $keepIds))
                        ->delete();
                });

            return Tab::make($title)->schema([
                $rulesRepeater,
            ]);
        })->all();

        return $schema->components([
            Group::make()->columnSpanFull()->schema([
                Grid::make()->columns(['default' => 1, 'lg' => 12])->gap(6)->schema([

                    // SOL
                    Group::make()->columnSpan(['default' => 12, 'lg' => 8])->schema([

                        // Dil sekmeleri
                        Tabs::make('i18n')->tabs(
                            collect($locales)->map(function (string $loc) use ($base) {
                                return Tab::make(strtoupper($loc))->schema([
                                    TextInput::make("name.$loc")
                                        ->label(__('admin.field.name'))
                                        ->required($loc === $base)
                                        ->live(debounce: 350),

                                    Textarea::make("description.$loc")
                                        ->label(__('admin.field.description'))
                                        ->rows(3),
                                ]);
                            })->all()
                        ),

                        // Kapasite
                        Section::make(__('admin.rooms.sections.capacity'))
                            ->columns(12)
                            ->schema([
                                Grid::make()->columns(3)->schema([
                                    TextInput::make('size_m2')
                                        ->label(__('admin.rooms.form.size_m2'))
                                        ->numeric(),

                                    TextInput::make('capacity_adults')
                                        ->label(__('admin.rooms.form.capacity_adults'))
                                        ->numeric()->default(0),

                                    TextInput::make('capacity_children')
                                        ->label(__('admin.rooms.form.capacity_children'))
                                        ->numeric()->default(0),
                                ])->columnSpan(12),
                            ]),

                        // Yataklar
                        Repeater::make('beds')
                            ->label(__('admin.rooms.form.bed_types'))
                            ->minItems(1)
                            ->defaultItems(1)
                            ->columns(12)
                            ->addActionLabel(__('admin.rooms.form.add_bed'))
                            ->reorderable(false)
                            ->afterStateHydrated(function ($component, $state, ?Room $record) {
                                if (! $record?->exists) return;
                                $rows = $record->beds
                                    ->map(fn (BedType $bt) => [
                                        'bed_type_id' => $bt->getKey(),
                                        'quantity' => (int) ($bt->pivot?->quantity ?? 1),
                                    ])->values()->all();
                                $component->state($rows);
                            })
                            ->saveRelationshipsUsing(function (Room $record, array $state) {
                                $sync = collect($state)
                                    ->filter(fn ($row) => ! empty($row['bed_type_id']))
                                    ->mapWithKeys(fn ($row) => [
                                        (int) $row['bed_type_id'] => ['quantity' => max(1, (int) ($row['quantity'] ?? 1))],
                                    ])->all();
                                $record->beds()->sync($sync);
                            })
                            ->schema([
                                Select::make('bed_type_id')
                                    ->label(__('admin.rooms.form.bed_type'))
                                    ->required()
                                    ->native(false)->searchable()->preload()
                                    ->options(function () {
                                        $ui = app()->getLocale();
                                        $base = config('app.locale', 'tr');
                                        return BedType::query()
                                            ->where('is_active', true)
                                            ->selectRaw("id, COALESCE(NULLIF(name->>?, ''), NULLIF(name->>?, '')) AS label", [$ui, $base])
                                            ->whereRaw("COALESCE(NULLIF(name->>?, ''), NULLIF(name->>?, '')) IS NOT NULL", [$ui, $base])
                                            ->orderBy('sort_order')->orderBy('label')
                                            ->pluck('label', 'id')
                                            ->map(fn ($v) => (string) $v)
                                            ->all();
                                    })
                                    ->columnSpan(9),

                                TextInput::make('quantity')
                                    ->label(__('admin.rooms.form.bed_qty'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->columnSpan(3),
                            ]),

                        // Oda Özellikleri
                        Section::make(__('admin.rooms.sections.facilities'))
                            ->columns(1)
                            ->schema([
                                Select::make('facilities')
                                    ->hiddenLabel()
                                    ->multiple()->native(false)->preload()->searchable()
                                    ->relationship('facilities', 'id')
                                    ->getOptionLabelFromRecordUsing(
                                        fn (RoomFacility $r) => (string) (
                                            $r->name[$uiLocale]
                                            ?? (array_values($r->name ?? [])[0] ?? '-')
                                        )
                                    ),
                            ]),

                        // Galeri
                        Section::make(__('admin.rooms.sections.gallery'))
                            ->columns(12)
                            ->schema([
                                SpatieMediaLibraryFileUpload::make('gallery')
                                    ->hiddenLabel()
                                    ->collection('gallery')
                                    ->image()
                                    ->multiple()
                                    ->panelLayout('grid')
                                    ->columnSpan(12),
                            ]),

                        // Fiyatlar (sol kolon, en altta)
                        Section::make(__('admin.rooms.sections.prices'))
                            ->columns(1)
                            ->schema(
                                ! empty($currencyTabs)
                                    ? [Tabs::make('prices')->tabs($currencyTabs)]
                                    : [
                                    TextEntry::make('no_currency_info')
                                        ->label(' ')
                                        ->state(__('admin.rooms.form.prices.no_active_price')),
                                ]
                            ),

                    ]),

                    // SAĞ
                    Group::make()->columnSpan(['default' => 12, 'lg' => 4])->schema([

                        Section::make(__('admin.rooms.sections.status'))
                            ->columns(1)
                            ->schema([
                                Toggle::make('is_active')->label(__('admin.field.is_active'))->default(true),
                                TextInput::make('sort_order')->label(__('admin.field.sort_order'))->numeric()->default(0),
                            ]),

                        Section::make(__('admin.rooms.sections.classification'))
                            ->columns(1)
                            ->schema([
                                Toggle::make('smoking')->label(__('admin.rooms.form.smoking'))->default(false),
                                Select::make('view_type_id')
                                    ->label(__('admin.rooms.form.view_type'))
                                    ->native(false)->searchable()->preload()
                                    ->options(
                                        ViewType::query()
                                            ->selectRaw("id, {$labelSql} AS label", [$uiLocale, $base])
                                            ->whereRaw("{$labelSql} IS NOT NULL", [$uiLocale, $base])
                                            ->orderBy('label')
                                            ->pluck('label', 'id')
                                            ->map(fn ($v) => (string) $v)
                                            ->all()
                                    ),
                            ]),

                        Section::make(__('admin.rooms.sections.hotel'))
                            ->columns(1)
                            ->schema([
                                Select::make('hotel_id')
                                    ->hiddenLabel()
                                    ->required()
                                    ->native(false)->searchable()->preload()
                                    ->options(
                                        Hotel::query()
                                            ->selectRaw(
                                                'id, COALESCE(name->>?, name->>?) AS label',
                                                [app()->getLocale(), config('app.locale', 'tr')]
                                            )
                                            ->orderBy('label')
                                            ->pluck('label', 'id')
                                            ->all()
                                    )
                                    ->default(fn () => request('hotel_id'))
                                    ->dehydrated(),
                            ]),

                        // Çocuk İndirimi
                        Section::make(__('admin.hotels.sections.child_sale'))
                            ->columns(1)
                            ->schema([
                                Toggle::make('child_discount_active')
                                    ->label(__('admin.field.is_active'))
                                    ->live()
                                    ->afterStateUpdated(function (bool $state, Set $set, Get $get) {
                                        if ($state && is_null($get('child_discount_percent'))) {
                                            $set('child_discount_percent', 50);
                                        }
                                        if (! $state) {
                                            $set('child_discount_percent', null);
                                        }
                                    }),

                                TextInput::make('child_discount_percent')
                                    ->label(__('admin.rooms.form.child_policy.sale_percent'))
                                    ->numeric()
                                    ->suffix('%')
                                    ->minValue(0)->maxValue(100)
                                    ->rules(['numeric','between:0,100'])
                                    ->live(debounce: 150)
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $v = is_numeric($state) ? (float) $state : null;
                                        if (! is_null($v)) {
                                            $set('child_discount_percent', max(0, min(100, $v)));
                                        }
                                    })
                                    ->afterStateHydrated(function ($state, Set $set, Get $get) {
                                        if ($get('child_discount_active') && is_null($state)) {
                                            $set('child_discount_percent', 50);
                                        }
                                    })
                                    ->required(fn (Get $get) => (bool) $get('child_discount_active'))
                                    ->disabled(fn (Get $get) => ! (bool) $get('child_discount_active'))
                                    ->helperText(__('admin.rooms.form.child_policy.child_sale_helper')),
                            ])
                    ]),
                ]),
            ]),
        ]);
    }
}
