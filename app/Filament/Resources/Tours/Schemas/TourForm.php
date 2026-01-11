<?php

namespace App\Filament\Resources\Tours\Schemas;

use App\Models\Currency;
use App\Models\TourCategory;
use App\Models\TourService;
use App\Support\Helpers\LocaleHelper;
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

class TourForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales  = LocaleHelper::active();
        $uiLocale = app()->getLocale();

        $currencyCodes = Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->pluck('code')
            ->map(fn (string $c) => strtoupper($c))
            ->values()
            ->all();

        $pricesSection = Section::make(__('admin.tours.sections.prices'))
            ->schema([
                Tabs::make('prices_tabs')->tabs(
                    collect($currencyCodes)->map(function (string $code) {
                        return Tab::make($code)->schema([
                            Grid::make()->columns(3)->schema([
                                TextInput::make("prices.$code.adult")
                                    ->label(__('admin.tours.form.adult'))
                                    ->numeric()
                                    ->minValue(0),

                                TextInput::make("prices.$code.child")
                                    ->label(__('admin.tours.form.child'))
                                    ->numeric()
                                    ->minValue(0),

                                TextInput::make("prices.$code.infant")
                                    ->label(__('admin.tours.form.infant'))
                                    ->numeric()
                                    ->minValue(0),
                            ]),
                        ]);
                    })->all()
                )->persistTabInQueryString(),
            ]);

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
                                                    ->label(__('admin.tours.form.name'))
                                                    ->required()
                                                    ->live(debounce: 300)
                                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old) use ($loc) {
                                                        $currentSlug = (string) ($get("slug.$loc") ?? '');
                                                        $oldSlugFromName = Str::slug((string) ($old ?? ''));

                                                        if ($currentSlug === '' || $currentSlug === $oldSlugFromName) {
                                                            $set("slug.$loc", Str::slug((string) ($state ?? '')));
                                                        }
                                                    }),

                                                TextInput::make("slug.$loc")
                                                    ->label(__('admin.tours.form.slug'))
                                                    ->required()
                                                    ->live(debounce: 250)
                                                    ->afterStateUpdated(function (Set $set, ?string $state) use ($loc) {
                                                        $set("slug.$loc", Str::slug((string) ($state ?? '')));
                                                    }),

                                                Textarea::make("short_description.$loc")
                                                    ->label(__('admin.tours.form.short_description'))
                                                    ->rows(3),

                                                Textarea::make("long_description.$loc")
                                                    ->label(__('admin.tours.form.long_description'))
                                                    ->rows(5),

                                                Repeater::make("notes.$loc")
                                                    ->label(__('admin.tours.form.notes'))
                                                    ->simple(
                                                        TextInput::make('value')->label(__('admin.tours.form.note'))
                                                    )
                                                    ->addActionLabel(__('admin.tours.form.add_note'))
                                                    ->reorderable(),
                                            ]);
                                        })->all()
                                    ),

                                    Section::make(__('admin.tours.sections.program'))
                                        ->columns(2)
                                        ->schema([
                                            TextInput::make('duration')
                                                ->label(__('admin.tours.form.duration'))
                                                ->placeholder('6 saat'),

                                            TextInput::make('start_time')
                                                ->label(__('admin.tours.form.start_time'))
                                                ->placeholder('09:00'),

                                            TextInput::make('min_age')
                                                ->numeric()
                                                ->label(__('admin.tours.form.min_age')),

                                            Select::make('days_of_week')
                                                ->label(__('admin.tours.form.days'))
                                                ->multiple()
                                                ->native(false)
                                                ->preload()
                                                ->searchable()
                                                ->options([
                                                    'mon' => __('admin.weekdays.mon'),
                                                    'tue' => __('admin.weekdays.tue'),
                                                    'wed' => __('admin.weekdays.wed'),
                                                    'thu' => __('admin.weekdays.thu'),
                                                    'fri' => __('admin.weekdays.fri'),
                                                    'sat' => __('admin.weekdays.sat'),
                                                    'sun' => __('admin.weekdays.sun'),
                                                ]),
                                        ]),

                                    Section::make(__('admin.tours.sections.services'))
                                        ->columns(1)
                                        ->schema([
                                            Select::make('included_service_ids')
                                                ->label(__('admin.tours.form.included'))
                                                ->multiple()
                                                ->native(false)
                                                ->preload()
                                                ->searchable()
                                                ->options(
                                                    TourService::query()
                                                        ->where('is_active', true)
                                                        ->orderBy('sort_order')
                                                        ->selectRaw("id, NULLIF(name->>'{$uiLocale}', '') AS label")
                                                        ->orderBy('label')
                                                        ->pluck('label', 'id')
                                                ),

                                            Select::make('excluded_service_ids')
                                                ->label(__('admin.tours.form.excluded'))
                                                ->multiple()
                                                ->native(false)
                                                ->preload()
                                                ->searchable()
                                                ->options(
                                                    TourService::query()
                                                        ->where('is_active', true)
                                                        ->orderBy('sort_order')
                                                        ->selectRaw("id, NULLIF(name->>'{$uiLocale}', '') AS label")
                                                        ->orderBy('label')
                                                        ->pluck('label', 'id')
                                                ),
                                        ]),

                                    Section::make(__('admin.tours.sections.gallery'))
                                        ->columns(12)
                                        ->schema([
                                            SpatieMediaLibraryFileUpload::make('gallery')
                                                ->hiddenLabel()
                                                ->collection('gallery')
                                                ->image()
                                                ->preserveFilenames()
                                                ->multiple()
                                                ->reorderable()
                                                ->panelLayout('grid')
                                                ->columnSpan(12),
                                        ]),

                                    ! empty($currencyCodes) ? $pricesSection : null,
                                ]),
                            // SAĞ (4)
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 4])
                                ->schema([
                                    Section::make(__('admin.tours.sections.status'))
                                        ->schema([
                                            Toggle::make('is_active')
                                                ->label(__('admin.tours.form.active'))
                                                ->default(true),

                                            TextInput::make('sort_order')
                                                ->numeric()
                                                ->label(__('admin.tours.form.sort_order'))
                                                ->default(0),

                                            TextInput::make('code')
                                                ->label(__('admin.tours.form.code'))
                                                ->disabled()
                                                ->helperText(__('admin.tours.form.code_help')),
                                        ]),

                                    Section::make(__('admin.tours.sections.cover'))
                                        ->schema([
                                            SpatieMediaLibraryFileUpload::make('cover')
                                                ->hiddenLabel()
                                                ->preserveFilenames()
                                                ->collection('cover')
                                                ->image()
                                                ->maxFiles(1),
                                        ]),

                                    Section::make(__('admin.tours.sections.classification'))
                                        ->schema([
                                            Select::make('tour_category_id')
                                                ->label(__('admin.tours.form.category'))
                                                ->native(false)
                                                ->preload()
                                                ->options(
                                                    TourCategory::query()
                                                        ->where('is_active', true)
                                                        ->orderBy('sort_order')
                                                        ->selectRaw("id, NULLIF(name->>'{$uiLocale}', '') AS label")
                                                        ->orderBy('label')
                                                        ->pluck('label', 'id')
                                                ),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
