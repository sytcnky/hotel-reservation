<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label(__('admin.field.first_name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('last_name')
                    ->label(__('admin.field.last_name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('email')
                    ->label(__('admin.field.email'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('phone')
                    ->label(__('admin.field.phone'))
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('admin.field.created_at'))
                    ->since(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
