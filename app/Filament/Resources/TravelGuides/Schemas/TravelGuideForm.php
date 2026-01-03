<?php

namespace App\Filament\Resources\TravelGuides\Schemas;

use App\Models\Hotel;
use App\Models\Tour;
use App\Models\Villa;
use App\Models\TravelGuide;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
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
use Filament\Forms\Components\Repeater;

class TravelGuideForm
{
    public static function configure(Schema $schema): Schema
    {
        $base = config('app.locale', 'tr');
        $locales = config('app.supported_locales', [$base]);
        $uiLocale = app()->getLocale();

        $pickLabel = function (?array $json) use ($uiLocale, $locales): string {
            if (! is_array($json)) {
                return '—';
            }

            $candidates = array_values(array_unique(array_filter(array_merge([$uiLocale], $locales))));
            foreach ($candidates as $loc) {
                $v = $json[$loc] ?? null;
                if (is_string($v) && trim($v) !== '') {
                    return trim($v);
                }
            }

            foreach ($json as $v) {
                if (is_string($v) && trim($v) !== '') {
                    return trim($v);
                }
            }

            return '—';
        };

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
                                    Tabs::make('i18n')
                                        ->tabs(
                                            collect($locales)->map(function (string $loc) use ($base) {
                                                $isBase = $loc === $base;

                                                return Tab::make(strtoupper($loc))->schema([
                                                    TextInput::make("title.$loc")
                                                        ->live(debounce: 350)
                                                        ->afterStateUpdated(function (?string $state, Set $set) use ($loc, $base) {
                                                            if (! filled($state)) return;

                                                            $set("slug.$loc", Str::slug($state));

                                                            if ($loc === $base) {
                                                                $set('canonical_slug', Str::slug($state));
                                                            }
                                                        }),

                                                    TextInput::make("slug.$loc")
                                                        ->label(__('admin.travel_guides.fields.slug'))
                                                        ->required($isBase)
                                                        ->dehydrateStateUsing(fn (?string $state) =>
                                                        $state ? Str::slug($state) : null
                                                        ),

                                                    Textarea::make("excerpt.$loc")
                                                        ->label(__('admin.travel_guides.fields.excerpt'))
                                                        ->rows(3),

                                                    TagsInput::make("tags.$loc")
                                                        ->label(__('admin.travel_guides.fields.tags'))
                                                        ->placeholder(__('admin.travel_guides.fields.tags_placeholder'))
                                                        ->suggestions([])
                                                        ->splitKeys([',', 'Enter'])
                                                        ->reorderable(),
                                                ]);
                                            })->all()
                                        ),

                                    Section::make(__('admin.travel_guides.sections.content'))
                                        ->columns(1)
                                        ->schema([
                                            Repeater::make('blocks')
                                                ->hiddenLabel()
                                                ->label(__('admin.travel_guides.fields.content_blocks'))
                                                ->relationship('blocks')
                                                ->reorderable()
                                                ->orderColumn('sort_order')
                                                ->collapsed()
                                                ->collapsible()
                                                ->defaultItems(0)
                                                ->addActionLabel(__('admin.travel_guides.actions.add_block'))
                                                ->itemLabel(function (array $state) use ($uiLocale, $base) {
                                                    $type = $state['type'] ?? null;

                                                    if ($type === 'content_section') {
                                                        $t = data_get($state, "data.title.$uiLocale")
                                                            ?: data_get($state, "data.title.$base");

                                                        return $t ? (string) $t : __('admin.travel_guides.blocks.content_section');
                                                    }

                                                    if ($type === 'recommendation') {
                                                        return __('admin.travel_guides.blocks.recommendation');
                                                    }

                                                    return __('admin.travel_guides.blocks.block');
                                                })
                                                ->schema([
                                                    Select::make('type')
                                                        ->label(__('admin.travel_guides.blocks.type'))
                                                        ->options([
                                                            'content_section' => __('admin.travel_guides.blocks.content_section'),
                                                            'recommendation'  => __('admin.travel_guides.blocks.recommendation'),
                                                        ])
                                                        ->native(false)
                                                        ->required()
                                                        ->live(),

                                                    // =========================
                                                    // 1) CONTENT SECTION BLOĞU
                                                    // =========================
                                                    Group::make()
                                                        ->visible(fn (Get $get) => $get('type') === 'content_section')
                                                        ->schema([
                                                            Select::make('data.layout')
                                                                ->label(__('admin.travel_guides.blocks.layout'))
                                                                ->options([
                                                                    'stacked'    => __('admin.travel_guides.blocks.layout_stacked'),
                                                                    'media_left' => __('admin.travel_guides.blocks.layout_media_left'),
                                                                ])
                                                                ->native(false)
                                                                ->required()
                                                                ->default('stacked'),

                                                            Tabs::make('block_i18n')
                                                                ->tabs(
                                                                    collect($locales)->map(function (string $loc) use ($base) {
                                                                        $isBase = $loc === $base;

                                                                        return Tab::make(strtoupper($loc))->schema([
                                                                            TextInput::make("data.title.$loc")
                                                                                ->label(__('admin.travel_guides.blocks.title'))
                                                                                ->required($isBase),

                                                                            Textarea::make("data.body.$loc")
                                                                                ->label(__('admin.travel_guides.blocks.body'))
                                                                                ->rows(8),
                                                                        ]);
                                                                    })->all()
                                                                ),

                                                            // Blok görseli: TravelGuideBlock modelindeki 'image' koleksiyonuna gider
                                                            SpatieMediaLibraryFileUpload::make('image')
                                                                ->label(__('admin.travel_guides.blocks.image'))
                                                                ->collection('image')
                                                                ->preserveFilenames()
                                                                ->image()
                                                                ->maxFiles(1),
                                                        ]),

                                                    // =========================
                                                    // 2) RECOMMENDATION BLOĞU
                                                    // =========================
                                                    Group::make()
                                                        ->visible(fn (Get $get) => $get('type') === 'recommendation')
                                                        ->schema([
                                                            Select::make('data.product_type')
                                                                ->label(__('admin.travel_guides.blocks.product_type'))
                                                                ->options([
                                                                    'hotel' => __('admin.travel_guides.blocks.product_type_hotel'),
                                                                    'villa' => __('admin.travel_guides.blocks.product_type_villa'),
                                                                ])
                                                                ->native(false)
                                                                ->required()
                                                                ->live(),

                                                            Select::make('data.product_id')
                                                                ->label(__('admin.travel_guides.blocks.product'))
                                                                ->native(false)
                                                                ->searchable()
                                                                ->preload()
                                                                ->required()
                                                                ->options(function (Get $get) use ($uiLocale, $base) {
                                                                    $type = $get('data.product_type');

                                                                    if ($type === 'hotel') {
                                                                        return Hotel::query()
                                                                            ->where('is_active', true)
                                                                            ->orderBy('sort_order')
                                                                            ->limit(500)
                                                                            ->get(['id', 'name'])
                                                                            ->mapWithKeys(function (Hotel $h) use ($uiLocale, $base) {
                                                                                $label = $h->name[$uiLocale] ?? ($h->name[$base] ?? null);
                                                                                return [$h->id => (string) $label];
                                                                            })
                                                                            ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                                                                            ->all();
                                                                    }

                                                                    if ($type === 'villa') {
                                                                        return Villa::query()
                                                                            ->where('is_active', true)
                                                                            ->orderBy('sort_order')
                                                                            ->limit(500)
                                                                            ->get(['id', 'name'])
                                                                            ->mapWithKeys(function (Villa $v) use ($uiLocale, $base) {
                                                                                $label = $v->name[$uiLocale] ?? ($v->name[$base] ?? null);
                                                                                return [$v->id => (string) $label];
                                                                            })
                                                                            ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                                                                            ->all();
                                                                    }

                                                                    return [];
                                                                })
                                                                ->disabled(fn (Get $get) => ! in_array($get('data.product_type'), ['hotel', 'villa'], true)),
                                                        ]),
                                                ]),
                                        ]),

                                ]),

                            // SAĞ (4)
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 4])
                                ->schema([
                                    Section::make(__('admin.travel_guides.sections.status'))
                                        ->columns(1)
                                        ->schema([
                                            Toggle::make('is_active')
                                                ->label(__('admin.field.is_active'))
                                                ->default(true),

                                            DateTimePicker::make('published_at')
                                                ->label(__('admin.travel_guides.fields.published_at')),

                                            TextInput::make('sort_order')
                                                ->label(__('admin.field.sort_order'))
                                                ->numeric()
                                                ->default(0),

                                            TextInput::make('canonical_slug')
                                                ->hidden()
                                                ->dehydrated(false),
                                        ]),

                                    Section::make(__('admin.travel_guides.sections.cover'))
                                        ->columns(1)
                                        ->schema([
                                            SpatieMediaLibraryFileUpload::make('cover')
                                                ->hiddenLabel()
                                                ->collection('cover')
                                                ->preserveFilenames()
                                                ->image()
                                                ->maxFiles(1),
                                        ]),

                                    Section::make(__('admin.travel_guides.sections.sidebar_tours'))
                                        ->columns(1)
                                        ->schema([
                                            Select::make('sidebar_tour_ids')
                                                ->label(__('admin.travel_guides.fields.sidebar_tours'))
                                                ->multiple()
                                                ->native(false)
                                                ->searchable()
                                                ->preload()
                                                ->options(function () use ($pickLabel): array {
                                                    return Tour::query()
                                                        ->where('is_active', true)
                                                        ->orderBy('sort_order')
                                                        ->limit(1000)
                                                        ->get(['id', 'name'])
                                                        ->mapWithKeys(fn (Tour $t) => [$t->id => $pickLabel(is_array($t->name) ? $t->name : null)])
                                                        ->all();
                                                }),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
