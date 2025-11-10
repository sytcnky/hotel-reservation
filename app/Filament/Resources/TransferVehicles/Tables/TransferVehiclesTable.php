<?php

namespace App\Filament\Resources\TransferVehicles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;

class TransferVehiclesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name.tr')->label('Ad')->sortable()->searchable(),
                TextColumn::make('capacity_total')->label('Kapasite')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('sort_order')->label('Sıra')->sortable(),
                TextColumn::make('created_at')->label('Oluşturma')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Güncelleme')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')->label('Silinmiş')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
