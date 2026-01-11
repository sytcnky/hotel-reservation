<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('first_name')
                ->label(__('admin.field.first_name'))
                ->required()
                ->maxLength(100),

            TextInput::make('last_name')
                ->label(__('admin.field.last_name'))
                ->required()
                ->maxLength(100),

            TextInput::make('phone')
                ->label(__('admin.field.phone'))
                ->tel()
                ->maxLength(20),

            TextInput::make('email')
                ->label(__('admin.field.email'))
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Select::make('locale')
                ->label(__('admin.field.locale'))
                ->options([
                    'tr' => __('admin.locales.tr'),
                    'en' => __('admin.locales.en'),
                ])
                ->required()
                ->native(false)
                ->default('tr'),

            TextInput::make('password')
                ->label(__('admin.field.password'))
                ->password()
                ->revealable()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                ->dehydrated(fn ($state) => filled($state))
                ->maxLength(255),
        ]);
    }
}
