<?php

namespace App\Filament\Resources\TravelGuides\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TravelGuidesTable
{
    public static function configure(Table $table): Table
    {
        $uiLocale = app()->getLocale();

        return $table
            ->columns([
                /**
                 * Başlık
                 * Kontrat: fallback yok. Sadece UI locale key'i.
                 */
                TextColumn::make('title_l')
                    ->label(__('admin.travel_guides.fields.title'))
                    ->state(fn ($record): string => (string) ($record->title_l ?? ''))
                    ->sortable(
                        query: function (Builder $q, string $dir) use ($uiLocale) {
                            return $q->orderByRaw("NULLIF(title->>'{$uiLocale}', '') {$dir}");
                        }
                    )
                    ->searchable(
                        query: function (Builder $q, string $search) use ($uiLocale) {
                            $like = '%' . $search . '%';

                            return $q->whereRaw(
                                "NULLIF(title->>'{$uiLocale}', '') ILIKE ?",
                                [$like]
                            );
                        }
                    ),

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
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
