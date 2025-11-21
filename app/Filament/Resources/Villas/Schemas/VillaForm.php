<?php

namespace App\Filament\Resources\Villas\Schemas;

use App\Forms\Components\IconPicker;
use App\Models\CancellationPolicy;
use App\Models\Location;
use App\Models\VillaAmenity;
use App\Models\VillaCategory;
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
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class VillaForm
{
    public static function configure(Schema $schema): Schema
    {
        $base     = config('app.locale', 'tr');
        $locales  = config('app.supported_locales', [$base]);
        $uiLocale = app()->getLocale();

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
                                    // Dil tabları – isim, slug, açıklama, öne çıkan özellikler, konaklama hakkında
                                    Tabs::make('i18n')->tabs(
                                        collect($locales)->map(function (string $loc) use ($base) {
                                            return Tab::make(strtoupper($loc))->schema([
                                                // Villa adı
                                                TextInput::make("name.$loc")
                                                    ->label(__('admin.villas.form.name'))
                                                    ->required()
                                                    ->live(debounce: 350)
                                                    ->afterStateUpdated(function (?string $state, callable $set) use ($loc) {
                                                        if (! filled($state)) {
                                                            return;
                                                        }

                                                        $set("slug_ui.$loc", Str::slug($state));
                                                    }),

                                                // Slug (UI)
                                                Group::make()
                                                    ->statePath('slug_ui')
                                                    ->schema([
                                                        TextInput::make($loc)
                                                            ->label(__('admin.villas.form.slug'))
                                                            ->required(),
                                                    ]),

                                                // Açıklama
                                                Textarea::make("description.$loc")
                                                    ->label(__('admin.villas.form.description'))
                                                    ->rows(4),

                                                // Öne Çıkan Özellikler – repeater
                                                Repeater::make("highlights.$loc")
                                                    ->label(__('admin.villas.form.highlights'))
                                                    ->simple(
                                                        TextInput::make('value')
                                                            ->label(__('admin.villas.form.highlight_item'))
                                                    )
                                                    ->addActionLabel(__('admin.villas.form.add_highlight'))
                                                    ->reorderable(),

                                                // Konaklama Hakkında – repeater
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

                                    // Özellik Grupları (VillaAmenity ile)
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
                                                data_get($state, "title.$base") ?: __('admin.villas.form.feature_group_untitled')
                                                )
                                                ->schema([
                                                    // Grup başlığı i18n
                                                    Tabs::make('fg_i18n')->tabs(
                                                        collect($locales)->map(
                                                            fn (string $loc) => Tab::make(strtoupper($loc))
                                                                ->schema([
                                                                    TextInput::make("title.$loc")
                                                                        ->label(__('admin.villas.form.feature_group_title'))
                                                                        ->required($loc === $base)
                                                                        ->live(onBlur: true),
                                                                ])
                                                        )->all()
                                                    ),

                                                    // VillaAmenity seçimi
                                                    Select::make('amenities')
                                                        ->label(__('admin.villas.form.amenities'))
                                                        ->multiple()
                                                        ->preload()
                                                        ->searchable()
                                                        ->relationship('amenities', 'id')
                                                        ->getOptionLabelFromRecordUsing(
                                                            fn (VillaAmenity $r) =>
                                                                $r->name[app()->getLocale()]
                                                                ?? array_values($r->name ?? [])[0]
                                                                ?? '—'
                                                        ),
                                                ]),
                                        ]),

                                    // Kapasiteler
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

                                    // Konum
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
                                                            $parts  = array_filter([$r->parent?->name, $r->parent?->parent?->name]);
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

                                                    $parts  = array_filter([$r->parent?->name, $r->parent?->parent?->name]);
                                                    $suffix = $parts ? ' (' . implode(', ', $parts) . ')' : '';

                                                    return $r->name . $suffix;
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

                                    // Yakın Çevre
                                    Section::make(__('admin.villas.sections.nearby'))
                                        ->columns(1)
                                        ->schema([
                                            Repeater::make('nearby')
                                                ->hiddenLabel()
                                                ->columns(12)
                                                ->collapsed()
                                                ->addActionLabel(__('admin.villas.form.add_nearby'))
                                                ->itemLabel(fn (array $state): string =>
                                                (string) ($state['label'][$uiLocale] ?? $state['label'][$base] ?? '—')
                                                )
                                                ->schema([
                                                    IconPicker::make('icon')
                                                        ->label(__('admin.villas.form.nearby_icon'))
                                                        ->variant('outline')
                                                        ->columnSpan(2),

                                                    Tabs::make('nearby_i18n')
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

                                    // İletişim
                                    Section::make(__('admin.villas.sections.contact'))
                                        ->columns(1)
                                        ->schema([
                                            TextInput::make('phone')
                                                ->label(__('admin.villas.form.phone')),
                                            TextInput::make('email')
                                                ->label(__('admin.villas.form.email'))
                                                ->email(),
                                        ]),

                                    // Galeri
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
                                ]),

                            // SAĞ KOLON (4)
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 4])
                                ->schema([
                                    // Durum
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
                                                ->helperText('Otomatik üretilir.'),
                                        ]),

                                    // Kapak görseli
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

                                    // Sınıflandırma
                                    Section::make(__('admin.villas.sections.classification'))
                                        ->columns(1)
                                        ->schema([
                                            Select::make('villa_category_id')
                                                ->label(__('admin.villas.form.category'))
                                                ->native(false)
                                                ->preload()
                                                ->options(
                                                    VillaCategory::query()
                                                        ->selectRaw("id, name->>'{$uiLocale}' AS label")
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
                                                        ->selectRaw("id, name->>'{$uiLocale}' AS label")
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
