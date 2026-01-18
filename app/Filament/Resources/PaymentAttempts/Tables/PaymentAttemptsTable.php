<?php

namespace App\Filament\Resources\PaymentAttempts\Tables;

use App\Models\PaymentAttempt;
use App\Support\Currency\CurrencyPresenter;
use App\Support\Date\DatePresenter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PaymentAttemptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.payment_attempts.table.id'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('status')
                    ->label(__('admin.payment_attempts.table.status'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => PaymentAttempt::labelForStatus($state))
                    ->color(fn (?string $state): string => PaymentAttempt::colorForStatus($state))
                    ->searchable(),

                TextColumn::make('order.code')
                    ->label(__('admin.payment_attempts.table.order'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->label(__('admin.payment_attempts.table.amount'))
                    ->formatStateUsing(function ($state, $record) {
                        if ($state === null) {
                            return '-';
                        }

                        return CurrencyPresenter::formatAdmin(
                            (float) $state,
                            $record->currency ?? null
                        );
                    })
                    ->sortable(),

                TextColumn::make('gateway')
                    ->label(__('admin.payment_attempts.table.gateway'))
                    ->searchable(),

                TextColumn::make('gateway_reference')
                    ->label(__('admin.payment_attempts.table.ref'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('started_at')
                    ->label(__('admin.payment_attempts.table.started'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable(),

                TextColumn::make('completed_at')
                    ->label(__('admin.payment_attempts.table.completed'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable(),

                TextColumn::make('error_code')
                    ->label(__('admin.payment_attempts.table.err'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ip_address')
                    ->label(__('admin.payment_attempts.table.ip'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label(__('admin.payment_attempts.table.created'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(PaymentAttempt::statusOptions()),

                SelectFilter::make('has_refund')
                    ->label(__('admin.payment_attempts.table.refund'))
                    ->options([
                        'yes' => __('admin.payment_attempts.filters.refunded'),
                        'no'  => __('admin.payment_attempts.filters.not_refunded'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! isset($data['value'])) {
                            return;
                        }

                        if ($data['value'] === 'yes') {
                            $query->whereHas('refundAttempts', function ($q) {
                                $q->where('status', \App\Models\RefundAttempt::STATUS_SUCCESS);
                            });
                        }

                        if ($data['value'] === 'no') {
                            $query->whereDoesntHave('refundAttempts', function ($q) {
                                $q->where('status', \App\Models\RefundAttempt::STATUS_SUCCESS);
                            });
                        }
                    }),


                SelectFilter::make('gateway')
                    ->options([
                        'demo'   => 'demo'
                    ])
                    ->searchable(),
            ]);
    }
}
