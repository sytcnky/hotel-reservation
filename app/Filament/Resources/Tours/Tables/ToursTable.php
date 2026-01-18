<?php

namespace App\Filament\Resources\Tours\Tables;

use App\Support\Date\DatePresenter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ToursTable
{
    public static function configure(Table $table): Table
    {
        $uiLocale = app()->getLocale();

        return $table
            ->columns([
                TextColumn::make('name_l')
                    ->label(__('admin.field.name'))
                    ->sortable(
                        query: fn (Builder $q, string $dir)
                        => $q->orderByRaw("NULLIF(name->>'{$uiLocale}', '') {$dir}")
                    )
                    ->searchable(
                        query: fn (Builder $q, string $search)
                        => $q->whereRaw(
                            "NULLIF(name->>'{$uiLocale}', '') ILIKE ?",
                            ['%' . $search . '%']
                        )
                    ),

                TextColumn::make('category.name_l')
                    ->label(__('admin.field.category'))
                    ->toggleable(),

                TextColumn::make('duration')
                    ->label(__('admin.tours.form.duration'))
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label(__('admin.field.is_active'))
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label(__('admin.field.sort_order'))
                    ->numeric()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('admin.field.created_at'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label(__('admin.field.updated_at'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->label(__('admin.field.deleted_at'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
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
