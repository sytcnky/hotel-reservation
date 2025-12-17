<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('admin.orders.table.code'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('admin.orders.table.status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label(__('admin.orders.table.payment_status'))
                    ->badge()
                    ->sortable(),

                TextColumn::make('currency')
                    ->label(__('admin.orders.table.currency'))
                    ->sortable(),

                TextColumn::make('total_amount')
                    ->label(__('admin.orders.table.total_amount'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('discount_amount')
                    ->label(__('admin.orders.table.discount_amount'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('is_guest')
                    ->label('Tip')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Misafir' : 'Üye')
                    ->color(fn ($state) => $state ? 'warning' : 'success')
                    ->sortable(),

                TextColumn::make('customer_name')
                    ->label(__('admin.orders.table.customer_name'))
                    ->searchable(),

                TextColumn::make('customer_email')
                    ->label(__('admin.orders.table.customer_email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('customer_phone')
                    ->label(__('admin.orders.table.customer_phone'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('paid_at')
                    ->label(__('admin.orders.table.paid_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('cancelled_at')
                    ->label(__('admin.orders.table.cancelled_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('admin.orders.table.created_at'))
                    ->dateTime()
                    ->sortable(),
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
