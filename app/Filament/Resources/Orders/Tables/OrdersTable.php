<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
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
                    ->formatStateUsing(function (?string $state): string {
                        $meta = Order::statusMeta($state);

                        return $meta['label_key']
                            ? __($meta['label_key'])
                            : (string) ($meta['label'] ?? $state ?? '-');
                    })
                    ->color(function (?string $state): string {
                        $meta = Order::statusMeta($state);

                        return (string) ($meta['filament_color'] ?? 'gray');
                    })
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

                TextColumn::make('customer_type')
                    ->label(__('admin.orders.table.type'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'guest'
                        ? __('admin.orders.table.guest')
                        : __('admin.orders.table.member')
                    )
                    ->color(fn (?string $state) => $state === 'guest' ? 'gray' : 'primary'),

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
            ->defaultSort('id', 'desc')
            ->filters([
                TrashedFilter::make(),
            ])
            ->toolbarActions([]);
    }
}
