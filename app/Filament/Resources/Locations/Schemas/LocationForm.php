<?php

namespace App\Filament\Resources\Locations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class LocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('type')
                ->label(__('admin.field.type'))
                ->options([
                    'country' => 'Ülke',
                    'province' => 'İl',
                    'district' => 'İlçe',
                    'area' => 'Bölge / Mahalle',
                ])
                ->required(),

            Select::make('parent_id')
                ->label(__('admin.field.parent'))
                ->relationship('parent', 'name')
                ->searchable()
                ->preload()
                ->nullable(),

            TextInput::make('code')
                ->label(__('admin.field.code'))
                ->maxLength(16)
                ->nullable(),

            TextInput::make('name')
                ->label(__('admin.field.name'))
                ->required()
                ->maxLength(255)
                ->live(debounce: 300)
                ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug((string) $state))),

            TextInput::make('slug')
                ->label(__('admin.field.slug'))
                ->required()
                ->maxLength(255)
                ->dehydrateStateUsing(fn ($state) => Str::slug((string) $state)),

            TextInput::make('path')
                ->label(__('admin.field.path'))
                ->disabled()
                ->helperText('Otomatik oluşturulur.')
                ->maxLength(255),

            Toggle::make('is_active')
                ->label(__('admin.field.is_active'))
                ->default(true),

            TextInput::make('sort_order')
                ->label(__('admin.field.sort_order'))
                ->numeric()
                ->default(0),
        ]);
    }
}
