<?php

namespace App\Filament\Resources\TravelGuides\Schemas;

use App\Models\Hotel;
use App\Models\Tour;
use App\Models\TravelGuide;
use App\Models\Villa;
use App\Support\Helpers\LocaleHelper;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
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

class TravelGuideForm
{
    public static function configure(Schema $schema): Schema
    {
        $uiLocale = app()->getLocale();
        $locales  = LocaleHelper::active();

        $pickLabel = function (?array $json) use ($uiLocale): string {
            if (! is_array($json)) {
                return '';
            }

            $v = $json[$uiLocale] ?? null;

            return is_string($v) ? trim($v) : '';
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
                                            collect($locales)->map(function (string $loc) {
                                                return Tab::make(strtoupper($loc))->schema([
                                                    TextInput::make("title.$loc")
                                                        ->label(__('admin.travel_guides.fields.title'))
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->live(debounce: 350)
                                                        ->afterStateUpdated(function (?string $state, Set $set) use ($loc): void {
                                                            if (! filled($state)) {
                                                                return;
                                                            }

                                                            $set("slug.$loc", Str::slug((string) $state));
                                                        }),

                                                    TextInput::make("slug.$loc")
                                                        ->label(__('admin.travel_guides.fields.slug'))
                                                        ->required()
                                                        ->maxLength(255)
                                                        ->live(debounce: 300)
                                                        ->afterStateUpdated(function (Set $set, ?string $state) use ($loc): void {
                                                            $set("slug.$loc", $state ? Str::slug((string) $state) : null);
                                                        })
                                                        ->dehydrateStateUsing(fn (?string $state) => $state ? Str::slug((string) $state) : null),

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
                                                ->itemLabel(function (array $state) use ($uiLocale): string {
                                                    $type = $state['type'] ?? null;

                                                    if ($type === 'content_section') {
                                                        $t = data_get($state, "data.title.$uiLocale");
                                                        return (is_string($t) && trim($t) !== '')
                                                            ? (string) $t
                                                            : __('admin.travel_guides.blocks.content_section');
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

                                                    // 1) CONTENT SECTION
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

                                                            Tabs::make('i18n')
                                                                ->tabs(
                                                                    collect($locales)->map(function (string $loc) {
                                                                        return Tab::make(strtoupper($loc))->schema([
                                                                            TextInput::make("data.title.$loc")
                                                                                ->label(__('admin.travel_guides.blocks.title'))
                                                                                ->required()
                                                                                ->maxLength(255),

                                                                            Textarea::make("data.body.$loc")
                                                                                ->label(__('admin.travel_guides.blocks.body'))
                                                                                ->rows(8),
                                                                        ]);
                                                                    })->all()
                                                                ),

                                                            SpatieMediaLibraryFileUpload::make('image')
                                                                ->label(__('admin.travel_guides.blocks.image'))
                                                                ->collection('image')
                                                                ->preserveFilenames()
                                                                ->image()
                                                                ->maxFiles(1),
                                                        ]),

                                                    // 2) RECOMMENDATION
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
                                                                ->options(function (Get $get) use ($pickLabel): array {
                                                                    $type = $get('data.product_type');

                                                                    if ($type === 'hotel') {
                                                                        return Hotel::query()
                                                                            ->where('is_active', true)
                                                                            ->orderBy('sort_order')
                                                                            ->limit(500)
                                                                            ->get(['id', 'name'])
                                                                            ->mapWithKeys(function (Hotel $h) use ($pickLabel) {
                                                                                $label = $pickLabel(is_array($h->name) ? $h->name : null);
                                                                                return [$h->id => $label];
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
                                                                            ->mapWithKeys(function (Villa $v) use ($pickLabel) {
                                                                                $label = $pickLabel(is_array($v->name) ? $v->name : null);
                                                                                return [$v->id => $label];
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
                                                        ->mapWithKeys(function (Tour $t) use ($pickLabel) {
                                                            $label = $pickLabel(is_array($t->name) ? $t->name : null);
                                                            return [$t->id => $label];
                                                        })
                                                        ->filter(fn ($v) => is_string($v) && trim($v) !== '')
                                                        ->all();
                                                }),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
