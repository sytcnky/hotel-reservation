<?php

namespace App\Filament\Resources\Rooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class RoomsTable
{
    public static function configure(Table $table): Table
    {
        $base = config('app.locale', 'tr');
        $ui = app()->getLocale();

        $loc = static function (null|array|string $val) use ($ui, $base): ?string {
            if (is_array($val)) {
                return $val[$ui] ?? ($val[$base] ?? (array_values($val)[0] ?? null));
            }

            return is_string($val) ? $val : null;
        };

        return $table
            ->columns([
                // Ad
                TextColumn::make('name_i18n')
                    ->label(__('admin.field.name'))
                    ->state(fn ($record) => $loc($record?->name))
                    ->searchable()
                    ->sortable('id'),

                // Otel
                TextColumn::make('hotel_label')
                    ->label(__('admin.field.hotel'))
                    ->state(fn ($record) => $loc($record?->hotel?->name))
                    ->sortable('hotel_id'),

                // Manzara
                TextColumn::make('view_type_label')
                    ->label(__('admin.rooms.form.view_type'))
                    ->state(fn ($record) => $loc($record?->viewType?->name))
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('admin.field.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sort_order')
                    ->label(__('admin.field.sort_order'))
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('admin.field.is_active'))
                    ->boolean(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
