<?php

namespace App\Filament\Resources\Villas\Schemas;

use App\Forms\Components\IconPicker;
use App\Models\CancellationPolicy;
use App\Models\Currency;
use App\Models\Location;
use App\Models\Villa;
use App\Models\VillaAmenity;
use App\Models\VillaCategory;
use App\Models\VillaRateRule;
use App\Support\Helpers\LocaleHelper;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VillaForm
{
    public static function configure(Schema $schema): Schema
    {
        $uiLocale = app()->getLocale();
        $locales  = LocaleHelper::active();

        $currencies = Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        // Hafta günleri (1=Mon ... 7=Sun)
        $weekdayOptions = [
            1 => __('admin.weekdays.mon'),
            2 => __('admin.weekdays.tue'),
            3 => __('admin.weekdays.wed'),
            4 => __('admin.weekdays.thu'),
            5 => __('admin.weekdays.fri'),
            6 => __('admin.weekdays.sat'),
            7 => __('admin.weekdays.sun'),
        ];

        $currencyTabs = $currencies->map(function (Currency $c) use ($weekdayOptions) {
            $title = trim($c->code . ' – ' . ((string) ($c->name_l ?? '')));

            $rulesRepeater = Repeater::make('rates_' . $c->id)
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
                                    ->minDate(fn (Get $get) => $get('date_start'))
                                    ->columnSpan(6),

                                CheckboxList::make('weekdays')
                                    ->label(__('admin.rooms.form.prices.days'))
                                    ->options($weekdayOptions)
                                    ->default([1, 2, 3, 4, 5, 6, 7])
                                    ->columns(7)
                                    ->columnSpan(12),
                            ])
                            ->secondary()
                            ->compact(),

                        TextInput::make('amount')
                            ->label(fn () => __('admin.rooms.form.prices.price', [
                                'currency' => (string) $c->code,
                            ]))
                            ->numeric()
                            ->minValue(0)
                            ->required(false)
                            ->disabled(fn (Get $get) => (bool) $get('closed'))
                            ->columnSpan(12),

                        TextInput::make('min_nights')
                            ->label(__('admin.villas.form.min_nights'))
                            ->numeric()
                            ->minValue(1)
                            ->columnSpan(6),

                        TextInput::make('max_nights')
                            ->label(__('admin.villas.form.max_nights'))
                            ->numeric()
                            ->minValue(1)
                            ->columnSpan(6),

                        Toggle::make('closed')
                            ->label(__('admin.rooms.form.prices.closed'))
                            ->helperText(__('admin.rooms.form.prices.closed_helper'))
                            ->live()
                            ->columnSpan(4),

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
                            ->columnSpan(12),
                    ]),
                ])
                ->afterStateHydrated(function ($component, $state, ?Villa $record) use ($c) {
                    if (! $record?->exists) {
                        return;
                    }

                    $rows = $record->rateRules()
                        ->where('currency_id', $c->id)
                        ->orderBy('priority', 'desc')
                        ->orderBy('date_start')
                        ->get()
                        ->map(function (VillaRateRule $r) {
                            $days = [];
                            for ($i = 1; $i <= 7; $i++) {
                                if ($r->weekday_mask & (1 << ($i - 1))) {
                                    $days[] = $i;
                                }
                            }

                            return [
                                'id'          => $r->id,
                                'label'       => $r->label,
                                'currency_id' => $r->currency_id,
                                'date_start'  => $r->date_start?->format('Y-m-d'),
                                'date_end'    => $r->date_end?->format('Y-m-d'),
                                'weekdays'    => $days,
                                'amount'      => $r->amount,
                                'closed'      => $r->closed,
                                'cta'         => $r->cta,
                                'ctd'         => $r->ctd,
                                'priority'    => $r->priority,
                                'note'        => $r->note,
                                'min_nights'  => $r->min_nights,
                                'max_nights'  => $r->max_nights,
                            ];
                        })
                        ->values()
                        ->all();

                    $component->state($rows);
                })
                ->saveRelationshipsUsing(function (Villa $record, array $state) use ($c) {
                    $nullIfEmpty = fn ($v) => ($v === '' || $v === null) ? null : $v;

                    $keepIds = [];

                    foreach ($state as $row) {
                        $start = $nullIfEmpty($row['date_start'] ?? null);
                        $end   = $nullIfEmpty($row['date_end'] ?? null);

                        $attrs = [
                            'villa_id'     => $record->getKey(),
                            'label'        => $row['label'] ?? null,
                            'currency_id'  => $c->id,

                            'date_start'   => $start,
                            'date_end'     => $end,
                            'weekday_mask' => VillaRateRule::weekdaysToMask($row['weekdays'] ?? []),

                            'amount'       => ! empty($row['closed']) ? 0 : (float) ($row['amount'] ?? 0),

                            'closed'       => (bool) ($row['closed'] ?? false),
                            'cta'          => (bool) ($row['cta'] ?? false),
                            'ctd'          => (bool) ($row['ctd'] ?? false),

                            'priority'     => (int) ($row['priority'] ?? 10),
                            'note'         => $nullIfEmpty($row['note'] ?? null),
                            'is_active'    => true,

                            'min_nights'   => $row['min_nights'] !== null && $row['min_nights'] !== ''
                                ? (int) $row['min_nights']
                                : null,

                            'max_nights'   => $row['max_nights'] !== null && $row['max_nights'] !== ''
                                ? (int) $row['max_nights']
                                : null,
                        ];

                        $id = $row['id'] ?? null;

                        if ($id) {
                            $record->rateRules()->whereKey($id)->update($attrs);
                            $keepIds[] = (int) $id;
                        } else {
                            $created   = $record->rateRules()->create($attrs);
                            $keepIds[] = $created->getKey();
                        }
                    }

                    $record->rateRules()
                        ->where('currency_id', $c->id)
                        ->when(! empty($keepIds), fn ($q) => $q->whereNotIn('id', $keepIds))
                        ->delete();
                });

            return Tab::make($title)->schema([$rulesRepeater]);
        })->all();

        return $schema->components([
            Group::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make()
                        ->columns(['default' => 1, 'lg' => 12])
                        ->gap(6)
                        ->schema([
                            // SOL (8)
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 8])
                                ->schema([
                                    Tabs::make('i18n')->tabs(
                                        collect($locales)->map(function (string $loc) {
                                            return Tab::make(strtoupper($loc))->schema([
                                                TextInput::make("name.$loc")
                                                    ->label(__('admin.villas.form.name'))
                                                    ->required()
                                                    ->live(debounce: 350)
                                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old) use ($loc) {
                                                        $currentSlug = (string) ($get("slug.$loc") ?? '');
                                                        $oldSlugFromName = Str::slug((string) ($old ?? ''));

                                                        if ($currentSlug === '' || $currentSlug === $oldSlugFromName) {
                                                            $set("slug.$loc", Str::slug((string) ($state ?? '')));
                                                        }
                                                    }),

                                                TextInput::make("slug.$loc")
                                                    ->label(__('admin.villas.form.slug'))
                                                    ->required()
                                                    ->live(debounce: 350)
                                                    ->afterStateUpdated(function (Set $set, ?string $state) use ($loc) {
                                                        $set("slug.$loc", $state ? Str::slug((string) $state) : null);
                                                    }),

                                                Textarea::make("description.$loc")
                                                    ->label(__('admin.villas.form.description'))
                                                    ->rows(4),

                                                Repeater::make("highlights.$loc")
                                                    ->label(__('admin.villas.form.highlights'))
                                                    ->simple(
                                                        TextInput::make('value')
                                                            ->label(__('admin.villas.form.highlight_item'))
                                                    )
                                                    ->addActionLabel(__('admin.villas.form.add_highlight'))
                                                    ->reorderable(),

                                                Repeater::make("stay_info.$loc")
                                                    ->label(__('admin.villas.form.stay_info'))
                                                    ->simple(
                                                        TextInput::make('value')
                                                            ->label(__('admin.villas.form.stay_info_item'))
                                                    )
                                                    ->addActionLabel(__('admin.villas.form.add_stay_info'))
                                                    ->reorderable(),
                                            ]);
                                        })->all()
                                    ),

                                    Section::make(__('admin.villas.sections.features'))
                                        ->columns(1)
                                        ->schema([
                                            Repeater::make('featureGroups')
                                                ->hiddenLabel()
                                                ->relationship('featureGroups')
                                                ->reorderable()
                                                ->orderColumn('sort_order')
                                                ->defaultItems(0)
                                                ->addActionLabel(__('admin.villas.form.add_feature_group'))
                                                ->itemLabel(fn (array $state): ?string =>
                                                (data_get($state, 'title.' . app()->getLocale()) ?: __('admin.villas.form.feature_group_untitled'))
                                                )
                                                ->schema([
                                                    Tabs::make('i18n')->tabs(
                                                        collect($locales)->map(
                                                            fn (string $loc) => Tab::make(strtoupper($loc))
                                                                ->schema([
                                                                    TextInput::make("title.$loc")
                                                                        ->label(__('admin.villas.form.feature_group_title'))
                                                                        ->required()
                                                                        ->live(onBlur: true),
                                                                ])
                                                        )->all()
                                                    ),

                                                    Select::make('amenities')
                                                        ->label(__('admin.villas.form.amenities'))
                                                        ->multiple()
                                                        ->preload()
                                                        ->searchable()
                                                        ->relationship('amenities', 'id')
                                                        ->getOptionLabelFromRecordUsing(function (VillaAmenity $r): string {
                                                            $ui = app()->getLocale();
                                                            return (string) ($r->name[$ui] ?? '');
                                                        }),
                                                ]),
                                        ]),

                                    Section::make(__('admin.villas.sections.capacities'))
                                        ->columns(3)
                                        ->schema([
                                            TextInput::make('max_guests')
                                                ->label(__('admin.villas.form.max_guests'))
                                                ->numeric()
                                                ->required(),

                                            TextInput::make('bedroom_count')
                                                ->label(__('admin.villas.form.bedroom_count'))
                                                ->numeric()
                                                ->required(),

                                            TextInput::make('bathroom_count')
                                                ->label(__('admin.villas.form.bathroom_count'))
                                                ->numeric()
                                                ->required(),
                                        ]),

                                    Section::make(__('admin.villas.sections.location'))
                                        ->columns(1)
                                        ->schema([
                                            Select::make('location_id')
                                                ->label(__('admin.villas.sections.location'))
                                                ->searchable()
                                                ->getSearchResultsUsing(function (string $search): array {
                                                    $q = Str::slug($search);

                                                    return Location::query()
                                                        ->with(['parent.parent'])
                                                        ->where('type', 'area')
                                                        ->where('is_active', true)
                                                        ->where('slug', 'ILIKE', "{$q}%")
                                                        ->limit(50)
                                                        ->get()
                                                        ->mapWithKeys(function (Location $r) {
                                                            $parts = array_filter([$r->parent?->name, $r->parent?->parent?->name]);
                                                            $suffix = $parts ? ' (' . implode(', ', $parts) . ')' : '';

                                                            return [$r->id => ((string) $r->name) . $suffix];
                                                        })
                                                        ->all();
                                                })
                                                ->getOptionLabelUsing(function ($value): ?string {
                                                    $r = Location::with(['parent.parent'])->find($value);
                                                    if (! $r) {
                                                        return null;
                                                    }

                                                    $parts = array_filter([$r->parent?->name, $r->parent?->parent?->name]);
                                                    $suffix = $parts ? ' (' . implode(', ', $parts) . ')' : '';

                                                    return ((string) $r->name) . $suffix;
                                                }),

                                            TextInput::make('address_line')
                                                ->label(__('admin.villas.form.address')),

                                            Grid::make()
                                                ->columns(2)
                                                ->schema([
                                                    TextInput::make('latitude')
                                                        ->label(__('admin.villas.form.latitude'))
                                                        ->numeric(),

                                                    TextInput::make('longitude')
                                                        ->label(__('admin.villas.form.longitude'))
                                                        ->numeric(),
                                                ]),
                                        ]),

                                    Section::make(__('admin.villas.sections.nearby'))
                                        ->columns(1)
                                        ->schema([
                                            Repeater::make('nearby')
                                                ->hiddenLabel()
                                                ->columns(12)
                                                ->collapsed()
                                                ->addActionLabel(__('admin.villas.form.add_nearby'))
                                                ->itemLabel(fn (array $state): string =>
                                                (string) (($state['label'][app()->getLocale()] ?? '') ?: '—')
                                                )
                                                ->schema([
                                                    IconPicker::make('icon')
                                                        ->label(__('admin.villas.form.nearby_icon'))
                                                        ->variant('outline')
                                                        ->columnSpan(2),

                                                    Tabs::make('i18n')
                                                        ->columnSpan(10)
                                                        ->tabs(
                                                            collect($locales)->map(function (string $loc) {
                                                                return Tab::make(strtoupper($loc))->schema([
                                                                    TextInput::make("label.$loc")
                                                                        ->label(__('admin.villas.form.nearby_label'))
                                                                        ->required(false),

                                                                    TextInput::make("distance.$loc")
                                                                        ->label(__('admin.villas.form.nearby_distance'))
                                                                        ->required(false),
                                                                ]);
                                                            })->all()
                                                        ),
                                                ]),
                                        ]),

                                    Section::make(__('admin.villas.sections.contact'))
                                        ->columns(2)
                                        ->schema([
                                            TextInput::make('phone')
                                                ->label(__('admin.villas.form.phone')),

                                            TextInput::make('email')
                                                ->label(__('admin.villas.form.email'))
                                                ->email(),
                                        ]),

                                    Section::make(__('admin.villas.sections.gallery'))
                                        ->columns(1)
                                        ->schema([
                                            TextInput::make('promo_video_id')
                                                ->label(__('admin.villas.form.promo_video_id'))
                                                ->maxLength(64)
                                                ->placeholder(__('admin.villas.form.promo_video_id_placeholder')),

                                            SpatieMediaLibraryFileUpload::make('gallery')
                                                ->hiddenLabel()
                                                ->collection('gallery')
                                                ->image()
                                                ->multiple()
                                                ->preserveFilenames()
                                                ->reorderable()
                                                ->panelLayout('grid')
                                                ->columnSpan(12),
                                        ]),

                                    Section::make(__('admin.rooms.sections.prices'))
                                        ->columns(1)
                                        ->schema([
                                            TextInput::make('prepayment_rate')
                                                ->label(__('admin.villas.form.prepayment_rate'))
                                                ->numeric()
                                                ->minValue(0)
                                                ->maxValue(100)
                                                ->suffix('%'),

                                            ! empty($currencyTabs)
                                                ? Tabs::make('villa_prices')->columnSpan(12)->tabs($currencyTabs)
                                                : TextEntry::make('no_currency_info')
                                                ->label(' ')
                                                ->state(__('admin.rooms.form.prices.no_active_price')),
                                        ]),
                                ]),

                            // SAĞ (4)
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 4])
                                ->schema([
                                    Section::make(__('admin.villas.sections.status'))
                                        ->columns(1)
                                        ->schema([
                                            Toggle::make('is_active')
                                                ->label(__('admin.villas.form.is_active'))
                                                ->default(true),

                                            TextInput::make('sort_order')
                                                ->label(__('admin.villas.form.sort_order'))
                                                ->numeric()
                                                ->default(0),

                                            TextInput::make('code')
                                                ->label(__('admin.villas.form.code'))
                                                ->disabled()
                                                ->helperText(__('admin.field.auto_generated')),
                                        ]),

                                    Section::make(__('admin.villas.form.cover'))
                                        ->columns(1)
                                        ->schema([
                                            SpatieMediaLibraryFileUpload::make('cover')
                                                ->hiddenLabel()
                                                ->collection('cover')
                                                ->preserveFilenames()
                                                ->image()
                                                ->maxFiles(1),
                                        ]),

                                    Section::make(__('admin.villas.sections.classification'))
                                        ->columns(1)
                                        ->schema([
                                            Select::make('villa_category_id')
                                                ->label(__('admin.villas.form.category'))
                                                ->native(false)
                                                ->preload()
                                                ->options(
                                                    VillaCategory::query()
                                                        ->selectRaw("id, NULLIF(name->>'{$uiLocale}', '') AS label")
                                                        ->orderBy('label')
                                                        ->pluck('label', 'id')
                                                ),

                                            Select::make('cancellation_policy_id')
                                                ->label(__('admin.villas.form.cancellation_policy'))
                                                ->native(false)
                                                ->preload()
                                                ->options(
                                                    CancellationPolicy::query()
                                                        ->where('is_active', true)
                                                        ->selectRaw("id, NULLIF(name->>'{$uiLocale}', '') AS label")
                                                        ->orderBy('sort_order')
                                                        ->pluck('label', 'id')
                                                ),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
