<?php

namespace App\Filament\Resources\TransferVehicles\Schemas;

use App\Support\Helpers\LocaleHelper;
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

class TransferVehicleForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = LocaleHelper::active();

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
                                        collect($locales)->map(fn (string $loc) =>
                                        Tab::make(strtoupper($loc))->schema([
                                            TextInput::make("name.$loc")
                                                ->label(__('admin.vehicles.form.name'))
                                                ->required(),

                                            Textarea::make("description.$loc")
                                                ->label(__('admin.vehicles.form.description'))
                                                ->rows(4),
                                        ])
                                        )->all()
                                    ),

                                    Section::make(__('admin.vehicles.sections.gallery'))
                                        ->schema([
                                            SpatieMediaLibraryFileUpload::make('gallery')
                                                ->hiddenLabel()
                                                ->collection('gallery')
                                                ->image()
                                                ->multiple()
                                                ->reorderable()
                                                ->panelLayout('grid')
                                                ->preserveFilenames(),
                                        ]),
                                ]),

                            // SAĞ (4)
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 4])
                                ->schema([
                                    Section::make(__('admin.vehicles.sections.status'))
                                        ->schema([
                                            Toggle::make('is_active')
                                                ->label(__('admin.vehicles.form.active'))
                                                ->default(true),

                                            TextInput::make('sort_order')
                                                ->label(__('admin.vehicles.form.sort_order'))
                                                ->numeric()
                                                ->default(0),
                                        ]),

                                    Section::make(__('admin.vehicles.sections.capacity'))
                                        ->columns(2)
                                        ->schema([
                                            TextInput::make('capacity_total')
                                                ->label(__('admin.vehicles.form.capacity_total'))
                                                ->numeric()
                                                ->minValue(1)
                                                ->required(),

                                            TextInput::make('capacity_adult_max')
                                                ->label(__('admin.vehicles.form.capacity_adult_max'))
                                                ->numeric()
                                                ->minValue(0)
                                                ->nullable(),

                                            TextInput::make('capacity_child_max')
                                                ->label(__('admin.vehicles.form.capacity_child_max'))
                                                ->numeric()
                                                ->minValue(0)
                                                ->nullable(),

                                            TextInput::make('capacity_infant_max')
                                                ->label(__('admin.vehicles.form.capacity_infant_max'))
                                                ->numeric()
                                                ->minValue(0)
                                                ->nullable(),
                                        ]),

                                    Section::make(__('admin.vehicles.form.cover'))
                                        ->schema([
                                            SpatieMediaLibraryFileUpload::make('cover')
                                                ->hiddenLabel()
                                                ->collection('cover')
                                                ->preserveFilenames()
                                                ->image()
                                                ->maxFiles(1),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
