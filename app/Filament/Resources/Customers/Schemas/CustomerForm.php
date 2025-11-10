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
                ->label('Ad')
                ->required()
                ->maxLength(100),

            TextInput::make('last_name')
                ->label('Soyad')
                ->required()
                ->maxLength(100),

            TextInput::make('phone')
                ->label('Telefon')
                ->tel()
                ->maxLength(20),

            TextInput::make('email')
                ->label('E-posta')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Select::make('locale')
                ->label('Dil')
                ->options([
                    'tr' => 'Türkçe',
                    'en' => 'English',
                ])
                ->required()
                ->native(false)
                ->default('tr'),

            TextInput::make('password')
                ->label('Parola')
                ->password()
                ->revealable()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                ->dehydrated(fn ($state) => filled($state))
                ->maxLength(255),
        ]);
    }
}
