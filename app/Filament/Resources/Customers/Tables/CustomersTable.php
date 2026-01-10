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
                TextColumn::make('first_name')->label('Ad')->sortable()->searchable(),
                TextColumn::make('last_name')->label('Soyad')->sortable()->searchable(),
                TextColumn::make('email')->label('E-posta')->sortable()->searchable(),
                TextColumn::make('phone')->label('Telefon')->toggleable(),
                TextColumn::make('created_at')->label('Oluşturulma')->since(),
            ])
            ->filters([ TrashedFilter::make() ])
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
