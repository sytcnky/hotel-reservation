<?php

namespace App\Filament\Resources\Hotels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HotelsTable
{
    public static function configure(Table $table): Table
    {
        $uiLocale = app()->getLocale();

        return $table
            ->columns([
                TextColumn::make('name_l')
                    ->label(__('admin.field.name'))
                    ->sortable(
                        query: fn (Builder $q, string $dir) => $q->orderByLocalized('name', $dir)
                    )
                    ->searchable(
                        query: fn (Builder $q, string $search) => $q->whereLocalizedLike('name', $search)
                    ),

                TextColumn::make('category_name_ui')
                    ->label(__('admin.field.category'))
                    ->state(fn ($record): string => (string) (($record->category?->name[$uiLocale] ?? '') ?: ''))
                    ->sortable(
                        query: fn (Builder $q, string $dir)
                        => $q
                            ->leftJoin('hotel_categories as hc', 'hc.id', '=', 'hotels.hotel_category_id')
                            ->orderByRaw("NULLIF(hc.name->>'{$uiLocale}', '') {$dir} NULLS LAST")
                            ->select('hotels.*')
                    )
                    ->searchable(
                        query: fn (Builder $q, string $search)
                        => $q->whereHas('category', fn (Builder $qq)
                        => $qq->whereRaw(
                            "NULLIF(name->>'{$uiLocale}', '') ILIKE ?",
                            ['%' . $search . '%']
                        )
                        )
                    ),

                TextColumn::make('starRating.name_l')
                    ->label(__('admin.hotels.form.star_rating'))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('area')
                    ->label(__('admin.field.area'))
                    ->state(fn ($record): string => (string) ($record->location?->name ?? ''))
                    ->toggleable(),

                TextColumn::make('district')
                    ->label(__('admin.field.district'))
                    ->state(fn ($record): string => (string) ($record->location?->parent?->name ?? ''))
                    ->toggleable(),

                TextColumn::make('province')
                    ->label(__('admin.field.province'))
                    ->state(fn ($record): string => (string) ($record->location?->parent?->parent?->name ?? ''))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sort_order')
                    ->label(__('admin.field.sort_order'))
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('admin.field.is_active'))
                    ->boolean(),

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
            ])
            ->filters([
                TrashedFilter::make(),
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
