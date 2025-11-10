<?php

namespace App\Filament\Resources\Locations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.field.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label(__('admin.field.slug'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label(__('admin.field.type'))
                    ->sortable(),

                TextColumn::make('parent.name')
                    ->label(__('admin.field.parent'))
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('code')
                    ->label(__('admin.field.code'))
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('admin.field.is_active'))
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label(__('admin.field.sort_order'))
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tür')
                    ->options([
                        'country' => 'Ülke',
                        'province' => 'İl',
                        'district' => 'İlçe',
                        'area' => 'Bölge / Mahalle',
                    ]),
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
