<?php

namespace App\Filament\Resources\UserResource\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.field.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('admin.field.email'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label(__('admin.field.phone') ?: 'Telefon')
                    ->toggleable(),

                TextColumn::make('roles.name')
                    ->label(__('admin.user.roles'))
                    ->separator(', '),

                TextColumn::make('created_at')
                    ->label(__('admin.field.created_at'))
                    ->dateTime()
                    ->since(),
            ]);
    }
}
