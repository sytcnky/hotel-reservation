<?php

namespace App\Filament\Resources\StaticPages\Forms;

use App\Models\Hotel;
use App\Models\Location;
use App\Models\TravelGuide;
use App\Support\Helpers\LocaleHelper;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class HomePageForm
{
    public static function schema(): array
    {
        $locales  = LocaleHelper::active();
        $uiLocale = app()->getLocale();

        $pickUi = function (mixed $value) use ($uiLocale): ?string {
            if (is_array($value)) {
                $v = $value[$uiLocale] ?? null;
                $v = is_string($v) ? trim($v) : null;

                return $v !== '' ? $v : null;
            }

            if ($value === null) {
                return null;
            }

            $v = is_string($value) ? trim($value) : (string) $value;

            return $v !== '' ? $v : null;
        };

        $getHotelLabelById = function (int $id) use ($pickUi): ?string {
            static $cache = [];

            if (array_key_exists($id, $cache)) {
                return $cache[$id];
            }

            $h = Hotel::query()->find($id);
            $cache[$id] = $h ? $pickUi($h->name) : null;

            return $cache[$id];
        };

        $getGuideLabelById = function (int $id) use ($pickUi): ?string {
            static $cache = [];

            if (array_key_exists($id, $cache)) {
                return $cache[$id];
            }

            $g = TravelGuide::query()->find($id);
            $cache[$id] = $g ? $pickUi($g->title) : null;

            return $cache[$id];
        };

        $searchHotels = function (string $search) use ($uiLocale): array {
            return Hotel::query()
                ->where('is_active', true)
                ->withoutTrashed()
                ->whereRaw("NULLIF(name->>'{$uiLocale}', '') ILIKE ?", ["%{$search}%"])
                ->limit(50)
                ->get()
                ->mapWithKeys(function (Hotel $h) use ($uiLocale) {
                    $name = null;

                    if (is_array($h->name ?? null)) {
                        $name = $h->name[$uiLocale] ?? null;
                    } elseif ($h->name !== null) {
                        $name = (string) $h->name;
                    }

                    $name = is_string($name) ? trim($name) : null;

                    return [$h->id => ($name !== '' && $name !== null ? $name : '—')];
                })
                ->all();
        };

        $searchGuides = function (string $search) use ($uiLocale): array {
            return TravelGuide::query()
                ->where('is_active', true)
                ->withoutTrashed()
                ->whereRaw("NULLIF(title->>'{$uiLocale}', '') ILIKE ?", ["%{$search}%"])
                ->limit(50)
                ->get()
                ->mapWithKeys(function (TravelGuide $g) use ($uiLocale) {
                    $title = null;

                    if (is_array($g->title ?? null)) {
                        $title = $g->title[$uiLocale] ?? null;
                    } elseif ($g->title !== null) {
                        $title = (string) $g->title;
                    }

                    $title = is_string($title) ? trim($title) : null;

                    return [$g->id => ($title !== '' && $title !== null ? $title : '—')];
                })
                ->all();
        };

        $tabs = function (array $fieldsByLocale) use ($locales): Tabs {
            return Tabs::make('i18n')->tabs(
                collect($locales)
                    ->map(fn (string $loc) => Tab::make(strtoupper($loc))->schema($fieldsByLocale[$loc] ?? []))
                    ->all()
            );
        };

        $locationNameUi = function (Location $loc) use ($uiLocale): string {
            $name = $loc->name;

            if (is_array($name)) {
                $v = $name[$uiLocale] ?? null;
                $v = is_string($v) ? trim($v) : null;

                return ($v !== '' && $v !== null) ? $v : '—';
            }

            $v = is_string($name) ? trim($name) : (string) $name;

            return $v !== '' ? $v : '—';
        };

        return [
            // =========================================================
            // 1) HERO
            // =========================================================
            Section::make(__('admin.static_pages.pages.home.hero'))
                ->schema([
                    $tabs(
                        collect($locales)->mapWithKeys(function (string $loc) {
                            return [$loc => [
                                TextInput::make("content.hero.eyebrow.$loc")
                                    ->label(__('admin.static_pages.form.eyebrow')),

                                TextInput::make("content.hero.title.$loc")
                                    ->label(__('admin.static_pages.form.title')),

                                TextInput::make("content.hero.subtitle.$loc")
                                    ->label(__('admin.static_pages.form.subtitle')),
                            ]];
                        })->all()
                    ),

                    Section::make(__('admin.static_pages.form.hero_media'))
                        ->schema([
                            Grid::make()
                                ->columns(['default' => 1, 'lg' => 12])
                                ->schema([
                                    SpatieMediaLibraryFileUpload::make('home_hero_background')
                                        ->label(__('admin.static_pages.form.hero_background_image'))
                                        ->collection('home_hero_background')
                                        ->preserveFilenames()
                                        ->image()
                                        ->maxFiles(1)
                                        ->columnSpan(['default' => 12, 'lg' => 6]),

                                    SpatieMediaLibraryFileUpload::make('home_hero_transparent')
                                        ->label(__('admin.static_pages.form.hero_transparent_image'))
                                        ->collection('home_hero_transparent')
                                        ->preserveFilenames()
                                        ->image()
                                        ->maxFiles(1)
                                        ->columnSpan(['default' => 12, 'lg' => 6]),
                                ]),
                        ]),
                ]),

            // =========================================================
            // 2) POPÜLER OTELLER
            // =========================================================
            Section::make(__('admin.static_pages.pages.home.popular_hotels'))
                ->schema([
                    $tabs(
                        collect($locales)->mapWithKeys(function (string $loc) {
                            return [$loc => [
                                TextInput::make("content.popular_hotels.section_eyebrow.$loc")
                                    ->label(__('admin.static_pages.form.section_eyebrow')),

                                TextInput::make("content.popular_hotels.section_title.$loc")
                                    ->label(__('admin.static_pages.form.section_title')),

                                TextInput::make("content.popular_hotels.hero_title.$loc")
                                    ->label(__('admin.static_pages.form.hero_title')),

                                Textarea::make("content.popular_hotels.description.$loc")
                                    ->label(__('admin.static_pages.form.description'))
                                    ->rows(4),

                                Grid::make()
                                    ->columns(['default' => 1, 'lg' => 12])
                                    ->schema([
                                        TextInput::make("content.popular_hotels.button.text.$loc")
                                            ->label(__('admin.static_pages.form.button_text'))
                                            ->columnSpan(['default' => 12, 'lg' => 6]),

                                        TextInput::make("content.popular_hotels.button.href.$loc")
                                            ->label(__('admin.static_pages.form.button_href'))
                                            ->columnSpan(['default' => 12, 'lg' => 6]),
                                    ]),
                            ]];
                        })->all()
                    ),

                    // HERO GÖRSEL (Popüler Oteller sol görsel alanı)
                    Grid::make()
                        ->columns(['default' => 1, 'lg' => 12])
                        ->schema([
                            SpatieMediaLibraryFileUpload::make('home_popular_hotels_hero')
                                ->label(__('admin.static_pages.form.hero_image'))
                                ->collection('home_popular_hotels_hero')
                                ->preserveFilenames()
                                ->image()
                                ->maxFiles(1)
                                ->columnSpan(['default' => 12, 'lg' => 12]),
                        ]),

                    Section::make(__('admin.static_pages.form.carousel_settings'))
                        ->schema([
                            Grid::make()
                                ->columns(['default' => 1, 'lg' => 12])
                                ->schema([
                                    Select::make('content.popular_hotels.carousel.mode')
                                        ->label(__('admin.static_pages.form.collection_mode'))
                                        ->options([
                                            'latest'      => __('admin.static_pages.form.collection_modes.latest'),
                                            'manual'      => __('admin.static_pages.form.collection_modes.manual'),
                                            'by_location' => __('admin.static_pages.form.collection_modes.by_location'),
                                        ])
                                        ->native(false)
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if ($state !== 'manual') {
                                                $set('content.popular_hotels.carousel.items', []);
                                            }

                                            if ($state !== 'by_location') {
                                                $set('content.popular_hotels.carousel.location_id', null);
                                            }

                                            if (! in_array($state, ['latest', 'by_location'], true)) {
                                                $set('content.popular_hotels.carousel.limit', null);
                                            }
                                        })
                                        ->columnSpan(['default' => 12, 'lg' => 6]),

                                    TextInput::make('content.popular_hotels.carousel.per_page')
                                        ->label(__('admin.static_pages.form.per_page'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(6)
                                        ->columnSpan(['default' => 12, 'lg' => 3]),

                                    TextInput::make('content.popular_hotels.carousel.limit')
                                        ->label(__('admin.static_pages.form.limit'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->visible(fn (Get $get) => in_array($get('content.popular_hotels.carousel.mode'), ['latest', 'by_location'], true))
                                        ->columnSpan(['default' => 12, 'lg' => 3]),
                                ]),

                            Select::make('content.popular_hotels.carousel.location_id')
                                ->label(__('admin.static_pages.form.location'))
                                ->searchable()
                                ->visible(fn (Get $get) => $get('content.popular_hotels.carousel.mode') === 'by_location')
                                ->getSearchResultsUsing(function (string $search) use ($locationNameUi): array {
                                    $q = Str::slug($search);

                                    return Location::query()
                                        ->with(['parent.parent'])
                                        ->where('type', 'area')
                                        ->where('is_active', true)
                                        ->where('slug', 'ILIKE', "{$q}%")
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(function (Location $r) use ($locationNameUi) {
                                            $parentName = $r->parent ? $locationNameUi($r->parent) : null;
                                            $gpName     = $r->parent?->parent ? $locationNameUi($r->parent->parent) : null;

                                            $parts  = array_filter([$parentName, $gpName], fn ($v) => is_string($v) && $v !== '' && $v !== '—');
                                            $suffix = $parts ? ' (' . implode(', ', $parts) . ')' : '';

                                            return [$r->id => $locationNameUi($r) . $suffix];
                                        })
                                        ->all();
                                })
                                ->getOptionLabelUsing(function ($value) use ($locationNameUi): ?string {
                                    $r = Location::with(['parent.parent'])->find($value);

                                    if (! $r) {
                                        return null;
                                    }

                                    $parentName = $r->parent ? $locationNameUi($r->parent) : null;
                                    $gpName     = $r->parent?->parent ? $locationNameUi($r->parent->parent) : null;

                                    $parts  = array_filter([$parentName, $gpName], fn ($v) => is_string($v) && $v !== '' && $v !== '—');
                                    $suffix = $parts ? ' (' . implode(', ', $parts) . ')' : '';

                                    return $locationNameUi($r) . $suffix;
                                }),

                            Repeater::make('content.popular_hotels.carousel.items')
                                ->label(__('admin.static_pages.form.manual_items'))
                                ->live()
                                ->visible(fn (Get $get) => $get('content.popular_hotels.carousel.mode') === 'manual')
                                ->reorderable()
                                ->defaultItems(0)
                                ->addActionLabel(__('admin.static_pages.form.add_manual_item'))
                                ->itemLabel(fn (array $state) => isset($state['id'])
                                    ? $getHotelLabelById((int) $state['id'])
                                    : null
                                )
                                ->schema([
                                    Select::make('id')
                                        ->label(__('admin.static_pages.form.item'))
                                        ->searchable()
                                        ->native(false)
                                        ->live()
                                        ->getSearchResultsUsing(fn (string $search) => $searchHotels($search))
                                        ->getOptionLabelUsing(fn ($value) => is_numeric($value)
                                            ? $getHotelLabelById((int) $value)
                                            : null
                                        ),
                                ]),
                        ])
                        ->collapsed(),
                ]),

            // =========================================================
            // 3) GEZİ REHBERİ
            // =========================================================
            Section::make(__('admin.static_pages.pages.home.travel_guides'))
                ->schema([
                    $tabs(
                        collect($locales)->mapWithKeys(function (string $loc) {
                            return [$loc => [
                                TextInput::make("content.travel_guides.hero_title.$loc")
                                    ->label(__('admin.static_pages.form.hero_title')),

                                TextInput::make("content.travel_guides.title.$loc")
                                    ->label(__('admin.static_pages.form.title')),

                                Textarea::make("content.travel_guides.description.$loc")
                                    ->label(__('admin.static_pages.form.description'))
                                    ->rows(4),
                            ]];
                        })->all()
                    ),

                    Section::make(__('admin.static_pages.form.grid_settings'))
                        ->schema([
                            Grid::make()
                                ->columns(['default' => 1, 'lg' => 12])
                                ->schema([
                                    Select::make('content.travel_guides.grid.mode')
                                        ->label(__('admin.static_pages.form.collection_mode'))
                                        ->options([
                                            'latest' => __('admin.static_pages.form.collection_modes.latest'),
                                            'manual' => __('admin.static_pages.form.collection_modes.manual'),
                                        ])
                                        ->native(false)
                                        ->live()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            if ($state !== 'manual') {
                                                $set('content.travel_guides.grid.items', []);
                                            }
                                        })
                                        ->columnSpan(['default' => 12, 'lg' => 6]),

                                    TextInput::make('content.travel_guides.grid.limit')
                                        ->label(__('admin.static_pages.form.limit'))
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(6)
                                        ->columnSpan(['default' => 12, 'lg' => 6]),
                                ]),

                            Repeater::make('content.travel_guides.grid.items')
                                ->label(__('admin.static_pages.form.manual_items'))
                                ->visible(fn (Get $get) => $get('content.travel_guides.grid.mode') === 'manual')
                                ->reorderable()
                                ->defaultItems(0)
                                ->addActionLabel(__('admin.static_pages.form.add_manual_item'))
                                ->itemLabel(fn (array $state) => isset($state['id'])
                                    ? $getGuideLabelById((int) $state['id'])
                                    : null
                                )
                                ->schema([
                                    Select::make('id')
                                        ->label(__('admin.static_pages.form.item'))
                                        ->searchable()
                                        ->native(false)
                                        ->live()
                                        ->getSearchResultsUsing(fn (string $search) => $searchGuides($search))
                                        ->getOptionLabelUsing(fn ($value) => is_numeric($value)
                                            ? $getGuideLabelById((int) $value)
                                            : null
                                        ),
                                ]),
                        ])
                        ->collapsed(),
                ]),
        ];
    }
}
