<?php

namespace App\Filament\Resources\TransferRoutes\Tables;

use App\Models\TransferRoute;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TransferRoutesTable
{
    public static function configure(Table $table): Table
    {
        $nameFromI18n = function ($v): ?string {
            // Admin panel locale tek kaynak
            $uiLocale = app()->getLocale();

            if (is_array($v)) {
                $s = $v[$uiLocale] ?? null;
                $s = is_string($s) ? trim($s) : null;

                return $s !== '' ? $s : null;
            }

            if (is_string($v)) {
                $s = trim($v);
                return $s !== '' ? $s : null;
            }

            return null;
        };

        return $table
            ->columns([
                TextColumn::make('from_location')
                    ->label(__('admin.routes.form.from'))
                    ->getStateUsing(fn (TransferRoute $record) => $nameFromI18n($record->from?->name) ?? '—'),

                TextColumn::make('to_location')
                    ->label(__('admin.routes.form.to'))
                    ->getStateUsing(fn (TransferRoute $record) => $nameFromI18n($record->to?->name) ?? '—'),

                TextColumn::make('duration_minutes')
                    ->label(__('admin.routes.form.duration_minutes'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('distance_km')
                    ->label(__('admin.routes.form.distance_km'))
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('admin.field.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('admin.field.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label(__('admin.field.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sort_order')
                    ->label(__('admin.routes.form.sort_order'))
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('admin.routes.form.active'))
                    ->boolean(),
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
                ]),
            ]);
    }
}
