<?php

namespace App\Filament\Resources\Languages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;

class LanguagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('flag')
                    ->label('Flag')
                    ->square()
                    ->disk('public')
                    ->imageSize(24),

                TextColumn::make('code')
                    ->label('Code')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('locale')
                    ->label('Locale')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('native_name')
                    ->label('Native')
                    ->sortable()
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label(__('admin.field.is_active'))
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label(__('admin.field.sort_order'))
                    ->numeric()
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
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
