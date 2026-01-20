<?php

namespace App\Filament\Resources\TransferRoutes\Schemas;

use App\Models\Currency;
use App\Models\Location;
use App\Models\TransferVehicle;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class TransferRouteForm
{
    public static function configure(Schema $schema): Schema
    {
        $currencyCodes = Currency::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->pluck('code')
            ->all();

        if (empty($currencyCodes)) {
            $currencyCodes = config('app.supported_currencies');
        }

        $currencyCodes = array_values(array_unique(array_map(fn ($c) => strtoupper((string) $c), (array) $currencyCodes)));

        return $schema->components([
            Group::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make()
                        ->columns(['default' => 1, 'lg' => 12])
                        ->gap(6)
                        ->schema([
                            // SOL
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 8])
                                ->schema([
                                    Section::make(__('admin.routes.sections.general'))
                                        ->columns()
                                        ->schema([
                                            Select::make('from_location_id')
                                                ->label(__('admin.routes.form.from'))
                                                ->native(false)
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
                                                })
                                                ->rule('exists:locations,id')
                                                ->required(),

                                            Select::make('to_location_id')
                                                ->label(__('admin.routes.form.to'))
                                                ->native(false)
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
                                                })
                                                ->rule('exists:locations,id')
                                                ->required(),
                                        ]),

                                    Section::make(__('admin.routes.sections.duration_distance'))
                                        ->columns()
                                        ->schema([
                                            TextInput::make('duration_minutes')
                                                ->label(__('admin.routes.form.duration_minutes'))
                                                ->numeric()
                                                ->minValue(0),

                                            TextInput::make('distance_km')
                                                ->label(__('admin.routes.form.distance_km'))
                                                ->numeric()
                                                ->minValue(0),
                                        ]),

                                    // YENI: Araç başı fiyatlar (pivot)
                                    Section::make(__('admin.routes.sections.prices'))
                                        ->schema([
                                            Repeater::make('vehicle_prices')
                                                ->label(__('admin.routes.sections.prices'))
                                                ->hiddenLabel()
                                                ->relationship('vehiclePrices')
                                                ->defaultItems(0)
                                                ->reorderable('sort_order')
                                                ->collapsed()
                                                ->itemLabel(function (array $state): ?string {
                                                    $vid = $state['transfer_vehicle_id'] ?? null;
                                                    if (! $vid) {
                                                        return null;
                                                    }

                                                    $v = TransferVehicle::query()->find($vid);
                                                    return $v?->name_l ?? ('#' . $vid);
                                                })
                                                ->schema([
                                                    Grid::make()
                                                        ->columns(12)
                                                        ->schema([
                                                            Select::make('transfer_vehicle_id')
                                                                ->label(__('admin.routes.form.vehicle') ?? 'Araç')
                                                                ->native(false)
                                                                ->searchable()
                                                                ->options(function (): array {
                                                                    return TransferVehicle::query()
                                                                        ->where('is_active', true)
                                                                        ->orderBy('capacity_total')
                                                                        ->orderBy('sort_order')
                                                                        ->orderBy('id')
                                                                        ->get()
                                                                        ->mapWithKeys(fn (TransferVehicle $v) => [$v->id => ($v->name_l ?? ('#' . $v->id))])
                                                                        ->all();
                                                                })
                                                                ->required()
                                                                ->columnSpan(6),

                                                            Toggle::make('is_active')
                                                                ->label(__('admin.routes.form.active'))
                                                                ->hidden()
                                                                ->default(true)
                                                                ->required()
                                                                ->columnSpan(3),

                                                            TextInput::make('sort_order')
                                                                ->label(__('admin.routes.form.sort_order'))
                                                                ->hidden()
                                                                ->numeric()
                                                                ->default(0)
                                                                ->columnSpan(3),
                                                        ]),

                                                    Tabs::make('currencies')
                                                        ->tabs(
                                                            collect($currencyCodes)->map(function (string $code) {
                                                                $code = strtoupper($code);

                                                                return Tab::make($code)->schema([
                                                                    TextInput::make("prices.$code")
                                                                        ->label(__('admin.routes.form.price') ?? 'Fiyat')
                                                                        ->numeric()
                                                                        ->minValue(0),
                                                                ]);
                                                            })->all()
                                                        )
                                                        ->persistTabInQueryString(),
                                                ]),
                                        ]),
                                ]),

                            // SAĞ
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 4])
                                ->schema([
                                    Section::make(__('admin.routes.sections.status'))
                                        ->columns(1)
                                        ->schema([
                                            Toggle::make('is_active')
                                                ->label(__('admin.routes.form.active'))
                                                ->default(true)
                                                ->required(),

                                            TextInput::make('sort_order')
                                                ->label(__('admin.routes.form.sort_order'))
                                                ->numeric()
                                                ->default(0),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
