<?php

namespace App\Filament\Resources\Languages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class LanguageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('code')
                ->label('Code')
                ->required()
                ->maxLength(8)
                ->rules(['alpha_dash'])
                ->helperText('Örn: tr, en, de'),

            TextInput::make('locale')
                ->label('Locale')
                ->maxLength(16)
                ->helperText('Örn: tr_TR, en_GB'),

            TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(64),

            TextInput::make('native_name')
                ->label('Native Name')
                ->maxLength(64),

            FileUpload::make('flag')
                ->label('Flag')
                ->image()
                ->disk('public')
                ->directory('flags')
                ->visibility('public')
                ->imageEditor()
                ->maxSize(1024),

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
