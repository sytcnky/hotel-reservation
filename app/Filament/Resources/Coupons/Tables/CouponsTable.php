<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Models\Coupon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        $base = config('app.locale', 'tr');
        $ui   = app()->getLocale();

        return $table
            ->columns([
                IconColumn::make('is_active')
                    ->label(__('admin.coupons.table.active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('code')
                    ->label(__('admin.coupons.table.code'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title')
                    ->label(__('admin.coupons.table.title'))
                    ->getStateUsing(function (Coupon $record) use ($ui, $base) {
                        return self::resolveLocalized($record->title, $ui, $base);
                    })
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('discount_summary')
                    ->label(__('admin.coupons.table.discount'))
                    ->getStateUsing(function (Coupon $record) {
                        if ($record->discount_type === 'percent') {
                            if ($record->percent_value === null) {
                                return null;
                            }

                            // Örnek: %10
                            $value = (float) $record->percent_value;
                            return '%' . rtrim(rtrim(number_format($value, 2, ',', ''), '0'), ',');
                        }

                        // Tutar tipi: currency_data içinden göster
                        $data = $record->currency_data ?? [];
                        if (empty($data) || !is_array($data)) {
                            return null;
                        }

                        $preferred = strtoupper(config('app.default_currency', 'TRY'));
                        $code = array_key_exists($preferred, $data)
                            ? $preferred
                            : (string) array_key_first($data);

                        $amount = $data[$code]['amount'] ?? null;
                        if ($amount === null) {
                            return null;
                        }

                        return number_format((float) $amount, 0, ',', '.') . ' ' . $code;
                    }),

                TextColumn::make('scope_type')
                    ->label(__('admin.coupons.table.scope_type'))
                    ->formatStateUsing(function (?string $state) {
                        return match ($state) {
                            'order_total'  => __('admin.coupons.form.scope_type_order_total'),
                            'product_type' => __('admin.coupons.form.scope_type_product_type'),
                            'product'      => __('admin.coupons.form.scope_type_product'),
                            default        => $state,
                        };
                    })
                    ->sortable(),

                TextColumn::make('valid_from')
                    ->label(__('admin.coupons.table.valid_from'))
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->label(__('admin.coupons.table.valid_until'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_exclusive')
                    ->label(__('admin.coupons.table.is_exclusive'))
                    ->boolean()
                    ->toggleable(),

                TextColumn::make('max_uses_per_user')
                    ->label(__('admin.coupons.table.max_uses_per_user'))
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('admin.coupons.table.created_at'))
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('admin.coupons.table.filter_active'))
                    ->boolean(),

                SelectFilter::make('discount_type')
                    ->label(__('admin.coupons.form.discount_type'))
                    ->options([
                        'percent' => __('admin.coupons.form.discount_type_percent'),
                        'amount'  => __('admin.coupons.form.discount_type_amount'),
                    ]),

                SelectFilter::make('scope_type')
                    ->label(__('admin.coupons.form.scope_type'))
                    ->options([
                        'order_total'  => __('admin.coupons.form.scope_type_order_total'),
                        'product_type' => __('admin.coupons.form.scope_type_product_type'),
                        'product'      => __('admin.coupons.form.scope_type_product'),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * JSON başlık alanını locale'e göre çöz.
     */
    protected static function resolveLocalized(mixed $value, string $ui, string $base): ?string
    {
        if (is_array($value)) {
            return $value[$ui] ?? $value[$base] ?? (string) (array_values($value)[0] ?? null);
        }

        if ($value !== null) {
            return (string) $value;
        }

        return null;
    }
}
