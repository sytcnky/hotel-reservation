<?php

namespace App\Filament\Resources\Coupons\Tables;

use App\Support\Currency\CurrencyPresenter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        $uiLocale = app()->getLocale();

        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('admin.coupons.table.code'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('title_l')
                    ->label(__('admin.coupons.table.title'))
                    ->sortable(
                        query: fn (Builder $q, string $dir) =>
                        $q->orderByRaw("NULLIF(title->>'{$uiLocale}', '') {$dir}")
                    )
                    ->searchable(
                        query: fn (Builder $q, string $search) =>
                        $q->whereRaw(
                            "NULLIF(title->>'{$uiLocale}', '') ILIKE ?",
                            ['%' . $search . '%']
                        )
                    )
                    ->limit(40),

                TextColumn::make('discount_summary')
                    ->label(__('admin.coupons.table.discount'))
                    ->getStateUsing(function ($record) {
                        if ($record->discount_type === 'percent') {
                            if ($record->percent_value === null) {
                                return null;
                            }

                            $value = (float) $record->percent_value;

                            return '%' . rtrim(rtrim(number_format($value, 2, ',', ''), '0'), ',');
                        }

                        $data = $record->currency_data ?? [];
                        if (! is_array($data) || $data === []) {
                            return null;
                        }

                        $parts = collect($data)
                            ->map(function ($row, $code) {
                                $amount = $row['amount'] ?? null;
                                if ($amount === null) {
                                    return null;
                                }

                                return CurrencyPresenter::formatAdmin($amount, (string) $code);
                            })
                            ->filter()
                            ->values()
                            ->all();

                        return $parts ? implode(' - ', $parts) : null;
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

                IconColumn::make('is_active')
                    ->label(__('admin.coupons.table.active'))
                    ->boolean()
                    ->sortable(),
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
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
