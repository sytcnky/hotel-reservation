<?php

namespace App\Filament\Resources\TransferVehicles\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class TransferVehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        $base = config('app.locale', 'tr');
        $locales = config('app.supported_locales', [$base]);

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
                                    // i18n sekmeleri
                                    Tabs::make('i18n')->tabs(
                                        collect($locales)->map(function (string $loc) use ($base) {
                                            return Tab::make(strtoupper($loc))->schema([
                                                TextInput::make("name.$loc")
                                                    ->label(__('admin.vehicles.form.name'))
                                                    ->required($loc === $base)
                                                    ->maxLength(150),

                                                Textarea::make("description.$loc")
                                                    ->label(__('admin.vehicles.form.description'))
                                                    ->rows(4),
                                            ]);
                                        })->all()
                                    ),

                                    // Galeri alta
                                    Section::make(__('admin.vehicles.sections.gallery'))
                                        ->columns(12)
                                        ->schema([
                                            SpatieMediaLibraryFileUpload::make('gallery')
                                                ->hiddenLabel()
                                                ->collection('gallery')
                                                ->image()
                                                ->multiple()
                                                ->reorderable()
                                                ->panelLayout('grid')
                                                ->columnSpan(12),
                                        ]),
                                ]),

                            // SAĞ KOLON (4)
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 4])
                                ->schema([
                                    Section::make(__('admin.vehicles.sections.status'))
                                        ->columns(1)
                                        ->schema([
                                            Toggle::make('is_active')->label(__('admin.vehicles.form.active'))->default(true),
                                            TextInput::make('sort_order')->label(__('admin.vehicles.form.sort_order'))->numeric()->default(0),
                                        ]),

                                    Section::make(__('admin.vehicles.sections.capacity'))
                                        ->columns(2)
                                        ->schema([
                                            TextInput::make('capacity_total')->label(__('admin.vehicles.form.capacity_total'))->numeric()->minValue(1)->required(),
                                            TextInput::make('capacity_adult_max')->label(__('admin.vehicles.form.capacity_adult_max'))->numeric()->minValue(0)->nullable(),
                                            TextInput::make('capacity_child_max')->label(__('admin.vehicles.form.capacity_child_max'))->numeric()->minValue(0)->nullable(),
                                            TextInput::make('capacity_infant_max')->label(__('admin.vehicles.form.capacity_infant_max'))->numeric()->minValue(0)->nullable(),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
