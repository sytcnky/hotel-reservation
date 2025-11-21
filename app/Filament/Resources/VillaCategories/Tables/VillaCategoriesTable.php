<?php

namespace App\Filament\Resources\VillaCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VillaCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('name_l')
                    ->label(__('admin.field.name'))
                    ->sortable()
                    ->searchable(),

                TextColumn::make('slug_l')
                    ->label(__('admin.field.slug'))
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

                SelectFilter::make('locale')
                    ->label(__('admin.filter.language'))
                    ->options([
                        'tr' => 'TR',
                        'en' => 'EN',
                    ])
                    ->query(function (Builder $query, $value) {
                        if (!$value) {
                            return;
                        }

                        $query->orderByRaw("name->>'$value' ASC");
                    }),
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
