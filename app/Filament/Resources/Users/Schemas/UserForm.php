<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('first_name')
                ->label(__('admin.field.first_name'))
                ->maxLength(100),

            TextInput::make('last_name')
                ->label(__('admin.field.last_name'))
                ->maxLength(100),

            TextInput::make('phone')
                ->label(__('admin.field.phone'))
                ->tel()
                ->maxLength(20),

            TextInput::make('email')
                ->label(__('admin.field.email'))
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            Select::make('locale')
                ->label(__('admin.user.locale'))
                ->options([
                    'tr' => __('admin.user.locale_tr'),
                    'en' => __('admin.user.locale_en'),
                ])
                ->native(false)
                ->required()
                ->default('tr'),

            TextInput::make('password')
                ->label(__('admin.field.password'))
                ->password()
                ->revealable()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                ->dehydrated(fn ($state) => filled($state))
                ->maxLength(255),

            Select::make('roles')
                ->label(__('admin.user.roles'))
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->searchable(),

            Select::make('permissions')
                ->label(__('admin.user.direct_permissions'))
                ->relationship('permissions', 'name')
                ->multiple()
                ->preload()
                ->searchable(),
        ]);
    }
}
