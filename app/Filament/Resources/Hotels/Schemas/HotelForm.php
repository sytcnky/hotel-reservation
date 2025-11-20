<?php

namespace App\Filament\Resources\Hotels\Schemas;

use App\Models\BeachType;
use App\Models\BoardType;
use App\Models\CancellationPolicy;
use App\Models\Facility;
use App\Models\HotelCategory;
use App\Models\HotelTheme;
use App\Models\Location;
use App\Models\PaymentOption;
use App\Models\StarRating;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use App\Forms\Components\IconPicker;


class HotelForm
{
    public static function configure(Schema $schema): Schema
    {
        $base = config('app.locale', 'tr');
        $locales = config('app.supported_locales', [$base]);
        $uiLocale = app()->getLocale();

        $boardOptions = BoardType::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->mapWithKeys(function (BoardType $b) use ($uiLocale, $base) {
                $name = $b->name[$uiLocale] ?? ($b->name[$base] ?? (array_values($b->name ?? [])[0] ?? ''));
                return [$b->id => (string) $name];
            })
            ->all();

        return $schema->components([
            Group::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make()
                        ->columns(['default' => 1, 'lg' => 12])
                        ->gap(6)
                        ->schema([
                            // SOL KOLON (8)
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 8])
                                ->schema([
                                    Tabs::make('i18n')->tabs(
                                        collect($locales)->map(function (string $loc) use ($base) {
                                            return Tab::make(strtoupper($loc))->schema([
                                                TextInput::make("name.$loc")
                                                    ->label(__('admin.hotels.form.name'))
                                                    ->required()
                                                    ->live(debounce: 350)
                                                    ->afterStateUpdated(function (?string $state, callable $set) use ($loc) {
                                                        if (! filled($state)) {
                                                            return;
                                                        }
                                                        $set("slug_ui.$loc", Str::slug($state));
                                                    }),

                                                Group::make()
                                                    ->statePath('slug_ui')
                                                    ->schema([
                                                        TextInput::make($loc)
                                                            ->label(__('admin.hotels.form.slug'))
                                                            ->required()
                                                    ]),

                                                Textarea::make("description.$loc")
                                                    ->label(__('admin.hotels.form.description'))
                                                    ->rows(4),

                                                Repeater::make("notes.$loc")
                                                    ->label(__('admin.hotels.form.notes'))
                                                    ->simple(
                                                        TextInput::make('value')->label(__('admin.hotels.form.notes'))
                                                    )
                                                    ->addActionLabel(__('admin.hotels.form.add_note'))
                                                    ->reorderable(),
                                            ]);
                                        })->all()
                                    ),

                                    Section::make(__('admin.hotels.sections.features'))
                                        ->columns(1)
                                        ->schema([
                                            Repeater::make('featureGroups')
                                                ->hiddenLabel()
                                                ->relationship('featureGroups')
                                                ->reorderable()
                                                ->orderColumn('sort_order')
                                                ->defaultItems(0)
                                                ->addActionLabel(__('admin.hotels.form.add_feature_group'))
                                                ->itemLabel(fn (array $state): ?string => data_get($state, "title.$base") ?: __('admin.hotels.form.feature_group_untitled')
                                                )
                                                ->schema([
                                                    Tabs::make('fg_i18n')->tabs(
                                                        collect($locales)->map(
                                                            fn (string $loc) => Tab::make(strtoupper($loc))
                                                                ->schema([
                                                                    TextInput::make("title.$loc")
                                                                        ->label(__('admin.hotels.form.feature_group_title'))
                                                                        ->required($loc === $base)->live(onBlur: true),
                                                                ])
                                                        )->all()
                                                    ),
                                                    Select::make('facilities')
                                                        ->label(__('admin.hotels.form.facilities'))
                                                        ->multiple()->preload()->searchable()
                                                        ->relationship('facilities', 'id')
                                                        ->getOptionLabelFromRecordUsing(
                                                            fn (Facility $r) => $r->name[app()->getLocale()]
                                                                ?? array_values($r->name ?? [])[0]
                                                                ?? '—'
                                                        ),
                                                ]),
                                        ]),

                                    Section::make(__('admin.hotels.sections.location'))
                                        ->columns(1)
                                        ->schema([
                                            Select::make('location_id')
                                                ->label(__('admin.hotels.sections.location'))
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

                                                            return [$r->id => $r->name . $suffix];
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

                                                    return $r->name . $suffix;
                                                }),
                                            TextInput::make('address_line')->label(__('admin.hotels.form.address')),
                                            Grid::make()->columns(2)->schema([
                                                TextInput::make('latitude')->label(__('admin.hotels.form.latitude'))->numeric(),
                                                TextInput::make('longitude')->label(__('admin.hotels.form.longitude'))->numeric(),
                                            ]),
                                        ]),

                                    // — YAKIN ÇEVRE —
                                    Section::make(__('admin.hotels.sections.nearby'))
                                        ->columns(1)
                                        ->schema([
                                            Repeater::make('nearby')
                                                ->hiddenLabel()
                                                ->columns(12)
                                                ->collapsed()
                                                ->addActionLabel(__('admin.hotels.form.add_nearby'))
                                                ->itemLabel(fn (array $state): string =>
                                                (string)($state['label'][$uiLocale] ?? $state['label'][$base] ?? '—')
                                                )
                                                ->schema([
                                                    IconPicker::make('icon')
                                                        ->label(__('admin.hotels.form.nearby_icon'))
                                                        ->variant('outline')
                                                        ->columnSpan(2),

                                                    // i18n alanlar
                                                    Tabs::make('nearby_i18n')
                                                        ->columnSpan(10)
                                                        ->tabs(
                                                            collect($locales)->map(function (string $loc) use ($base) {
                                                                return Tab::make(strtoupper($loc))->schema([
                                                                    TextInput::make("label.$loc")
                                                                        ->label(__('admin.hotels.form.nearby_label'))
                                                                        ->required(false), // istersen $loc === $base yaparsın

                                                                    TextInput::make("distance.$loc")
                                                                        ->label(__('admin.hotels.form.nearby_distance'))
                                                                        ->required(false),
                                                                ]);
                                                            })->all()
                                                        ),
                                                ]),
                                        ]),


                                    Section::make(__('admin.hotels.sections.contact'))
                                        ->columns(1)
                                        ->schema([
                                            TextInput::make('phone')->label(__('admin.hotels.form.phone')),
                                            TextInput::make('email')->label(__('admin.hotels.form.email'))->email(),
                                        ]),

                                    // Galeri
                                    Section::make(__('admin.hotels.sections.gallery'))
                                        ->columns(1)
                                        ->schema([
                                            TextInput::make('promo_video_id')
                                                ->label(__('admin.hotels.form.promo_video_id'))
                                                ->maxLength(64)
                                                ->placeholder(__('admin.hotels.form.promo_video_id_placeholder')),

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
                                ]),

                            // SAĞ KOLON (4)
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 4])
                                ->schema([

                                    Section::make(__('admin.hotels.sections.status'))
                                        ->columns(1)
                                        ->schema([
                                            Toggle::make('is_active')->label(__('admin.hotels.form.is_active'))->default(true),
                                            TextInput::make('sort_order')->label(__('admin.hotels.form.sort_order'))->numeric()->default(0),
                                            TextInput::make('code')->label(__('admin.hotels.form.code'))->disabled()->helperText('Otomatik üretilir.'),
                                        ]),

                                    // Kapak görseli buraya taşındı
                                    Section::make(__('admin.hotels.form.cover'))
                                        ->columns(1)
                                        ->schema([
                                            SpatieMediaLibraryFileUpload::make('cover')
                                                ->hiddenLabel()
                                                ->collection('cover')
                                                ->preserveFilenames()
                                                ->image()
                                                ->maxFiles(1),
                                        ]),

                                    Section::make(__('admin.hotels.sections.classification'))
                                        ->columns(1)
                                        ->schema([
                                            Select::make('hotel_category_id')
                                                ->label(__('admin.hotels.form.category'))
                                                ->native(false)->preload()
                                                ->options(
                                                    HotelCategory::query()
                                                        ->selectRaw("id, name->>'{$uiLocale}' AS label")
                                                        ->orderBy('label')->pluck('label', 'id')
                                                ),
                                            Select::make('star_rating_id')
                                                ->label(__('admin.hotels.form.star_rating'))
                                                ->native(false)->preload()
                                                ->options(
                                                    StarRating::query()
                                                        ->selectRaw("id, name->>'{$uiLocale}' AS label")
                                                        ->orderBy('label')->pluck('label', 'id')
                                                ),
                                            Select::make('board_type_id')
                                                ->label(__('admin.hotels.form.board_type'))
                                                ->native(false)->preload()
                                                ->options(
                                                    BoardType::query()
                                                        ->where('is_active', true)
                                                        ->selectRaw("id, name->>'{$uiLocale}' AS label")
                                                        ->orderBy('label')->pluck('label', 'id')
                                                ),
                                            Select::make('beach_type_id')
                                                ->label(__('admin.hotels.form.beach_type'))
                                                ->native(false)->preload()
                                                ->options(
                                                    BeachType::query()
                                                        ->where('is_active', true)
                                                        ->selectRaw("id, name->>'{$uiLocale}' AS label")
                                                        ->orderBy('label')->pluck('label', 'id')
                                                ),
                                            Select::make('paymentOptions')
                                                ->label(__('admin.hotels.form.payment_options'))
                                                ->multiple()->native(false)->preload()
                                                ->relationship('paymentOptions', 'id')
                                                ->getOptionLabelFromRecordUsing(
                                                    fn (PaymentOption $r) => $r->name[app()->getLocale()]
                                                        ?? array_values($r->name ?? [])[0]
                                                        ?? '—'
                                                ),
                                            Select::make('themes')
                                                ->label(__('admin.hotels.form.themes'))
                                                ->multiple()->native(false)->preload()
                                                ->relationship('themes', 'id')
                                                ->getOptionLabelFromRecordUsing(
                                                    fn (HotelTheme $r) => $r->name[app()->getLocale()]
                                                        ?? array_values($r->name ?? [])[0]
                                                        ?? '—'
                                                ),

                                            Select::make('cancellation_policy_id')
                                                ->label(__('admin.hotels.form.cancellation_policy'))
                                                ->native(false)
                                                ->preload()
                                                ->options(
                                                    CancellationPolicy::query()
                                                        ->where('is_active', true)
                                                        ->selectRaw("id, name->>'{$uiLocale}' AS label")
                                                        ->orderBy('sort_order')
                                                        ->pluck('label', 'id')
                                                ),
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
                                                ->label(__('admin.hotels.form.sale_percent'))
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
                                                ->helperText(__('admin.hotels.form.child_sale_helper')),
                                        ])
                                ]),
                        ]),
                ]),
        ]);
    }
}
