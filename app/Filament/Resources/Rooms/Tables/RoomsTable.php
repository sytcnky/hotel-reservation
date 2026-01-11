<?php

namespace App\Filament\Resources\Rooms\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoomsTable
{
    public static function configure(Table $table): Table
    {
        $uiLocale = app()->getLocale();

        return $table
            ->columns([
                // Ad
                TextColumn::make('name_l')
                    ->label(__('admin.field.name'))
                    ->state(fn ($record): string => (string) ($record->name_l ?? ''))
                    ->sortable(query: function (Builder $q, string $dir) use ($uiLocale) {
                        return $q->orderByRaw("NULLIF(name->>'{$uiLocale}', '') {$dir}");
                    })
                    ->searchable(query: function (Builder $q, string $search) use ($uiLocale) {
                        return $q->whereRaw("NULLIF(name->>'{$uiLocale}', '') ILIKE ?", ['%' . $search . '%']);
                    }),

                // Otel
                TextColumn::make('hotel_label')
                    ->label(__('admin.field.hotel'))
                    ->state(fn ($record): string => (string) ($record?->hotel?->name_l ?? ''))
                    ->sortable('hotel_id'),

                // Manzara
                TextColumn::make('view_type_label')
                    ->label(__('admin.rooms.form.view_type'))
                    ->state(fn ($record): string => (string) ($record?->viewType?->name_l ?? ''))
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
