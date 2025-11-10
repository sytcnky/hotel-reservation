<?php

namespace App\Filament\Resources\UserResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            // Ad / Soyad
            TextInput::make('first_name')
                ->label(__('admin.field.first_name') ?: 'Ad')
                ->maxLength(100),

            TextInput::make('last_name')
                ->label(__('admin.field.last_name') ?: 'Soyad')
                ->maxLength(100),

            // İletişim
            TextInput::make('phone')
                ->label(__('admin.field.phone') ?: 'Telefon')
                ->tel()
                ->maxLength(20),

            TextInput::make('email')
                ->label(__('admin.field.email') ?: 'E-posta')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            // Dil
            Select::make('locale')
                ->label(__('admin.user.locale') ?: 'Dil')
                ->options([
                    'tr' => __('admin.user.locale_tr') ?: 'Türkçe',
                    'en' => __('admin.user.locale_en') ?: 'English',
                ])
                ->native(false)
                ->required()
                ->default('tr'),

            // Şifre
            TextInput::make('password')
                ->label(__('admin.field.password') ?: 'Şifre')
                ->password()
                ->revealable()
                ->dehydrateStateUsing(fn ($state) => filled($state) ? $state : null)
                ->dehydrated(fn ($state) => filled($state))
                ->maxLength(255),

            // Roller / İzinler
            Select::make('roles')
                ->label(__('admin.user.roles') ?: 'Roller')
                ->relationship('roles', 'name')
                ->multiple()
                ->preload()
                ->searchable(),

            Select::make('permissions')
                ->label(__('admin.user.direct_permissions') ?: 'Doğrudan İzinler')
                ->relationship('permissions', 'name')
                ->multiple()
                ->preload()
                ->searchable(),
        ]);
    }
}
