<?php

namespace App\Filament\Resources\Languages\Tables;

use App\Support\Date\DatePresenter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LanguagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('flag')
                    ->label(__('admin.languages.table.flag'))
                    ->square()
                    ->disk('public')
                    ->imageSize(24)
                    ->toggleable(),

                TextColumn::make('code')
                    ->label(__('admin.languages.table.code'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('locale')
                    ->label(__('admin.languages.table.locale'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('name')
                    ->label(__('admin.languages.table.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('native_name')
                    ->label(__('admin.languages.table.native_name'))
                    ->sortable()
                    ->searchable()
                    ->toggleable(),

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
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
