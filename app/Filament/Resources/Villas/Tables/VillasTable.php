<?php

namespace App\Filament\Resources\Villas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VillasTable
{
    public static function configure(Table $table): Table
    {
        $uiLocale = app()->getLocale();

        return $table
            ->columns([
                /**
                 * Villa adı
                 * Kontrat: fallback yok. Sadece UI locale key'i.
                 */
                TextColumn::make('name_l')
                    ->label(__('admin.field.name'))
                    ->state(fn ($record): string => (string) ($record->name_l ?? ''))
                    ->sortable(
                        query: function (Builder $q, string $dir) use ($uiLocale) {
                            return $q->orderByRaw("NULLIF(name->>'{$uiLocale}', '') {$dir}");
                        }
                    )
                    ->searchable(
                        query: function (Builder $q, string $search) use ($uiLocale) {
                            $like = '%' . $search . '%';

                            return $q->whereRaw(
                                "NULLIF(name->>'{$uiLocale}', '') ILIKE ?",
                                [$like]
                            );
                        }
                    ),

                /**
                 * Kategori (villa_categories.name jsonb)
                 * Kontrat: fallback yok. Sadece UI locale key'i.
                 */
                TextColumn::make('category_name_ui')
                    ->label(__('admin.field.category'))
                    ->state(fn ($record): string => (string) (($record->category?->name[$uiLocale] ?? '') ?: ''))
                    ->sortable(
                        query: function (Builder $q, string $dir) use ($uiLocale) {
                            return $q
                                ->leftJoin('villa_categories as vc', 'vc.id', '=', 'villas.villa_category_id')
                                ->orderByRaw("NULLIF(vc.name->>'{$uiLocale}', '') {$dir}")
                                ->select('villas.*');
                        }
                    )
                    ->searchable(
                        query: function (Builder $q, string $search) use ($uiLocale) {
                            $like = '%' . $search . '%';

                            return $q->whereHas('category', function (Builder $qq) use ($uiLocale, $like) {
                                $qq->whereRaw("NULLIF(name->>'{$uiLocale}', '') ILIKE ?", [$like]);
                            });
                        }
                    ),

                /**
                 * Location isimleri string
                 */
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

                /**
                 * İptal politikası
                 * Not: cancellationPolicy.name_l accessor'ının da fallback'siz olduğundan emin olmalıyız.
                 */
                TextColumn::make('cancellationPolicy.name_l')
                    ->label(__('admin.villas.form.cancellation_policy'))
                    ->toggleable(),

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
