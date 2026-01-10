<?php

namespace App\Filament\Resources\Hotels\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class HotelsTable
{
    public static function configure(Table $table): Table
    {
        $localeOptions = collect(config('app.supported_locales', ['tr', 'en']))
            ->mapWithKeys(fn ($l) => [$l => strtoupper($l)])
            ->toArray();

        return $table
            ->columns([
                // Otel adı (JSON i18n)
                TextColumn::make('name_i18n')
                    ->label(__('admin.field.name'))
                    ->getStateUsing(function ($record) {
                        $v = $record->name;                 // array | string
                        $loc = app()->getLocale();
                        if (is_array($v)) {
                            return $v[$loc] ?? reset($v) ?: null;
                        }

                        return $v;
                    })
                    ->sortable(
                        query: function (Builder $q, string $dir) {
                            $loc = app()->getLocale();
                            $base = config('app.locale', 'tr');

                            return $q->orderByRaw("COALESCE(name->>?, name->>?) {$dir}", [$loc, $base]);
                        }
                    )
                    ->searchable(
                        query: function (Builder $q, string $search) {
                            $loc = app()->getLocale();
                            $base = config('app.locale', 'tr');
                            $like = '%' . $search . '%';

                            return $q->whereRaw(
                                '(name->>? ILIKE ? OR name->>? ILIKE ?)',
                                [$loc, $like, $base, $like]
                            );
                        }
                    ),

                // Kategori adı (ilişki)
                TextColumn::make('category.name_l')
                    ->label(__('admin.field.category'))
                    ->sortable(
                        query: fn (Builder $q, string $dir) => $q->join('hotel_categories as hc', 'hc.id', '=', 'hotels.hotel_category_id')
                            ->orderByLocalizedOn('hc.name', $dir) // proje makrosu varsayımı
                            ->select('hotels.*')
                    )
                    ->searchable(
                        query: fn (Builder $q, string $search) => $q->whereRelationLocalizedLike('category', 'name', $search)
                    ),

                // Yıldız (ilişki)
                TextColumn::make('starRating.name_l')
                    ->label(__('admin.hotels.form.star_rating'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                // Bölge
                TextColumn::make('area')
                    ->label(__('admin.field.area'))
                    ->getStateUsing(function ($record) {
                        $v = $record->location?->name;
                        if (is_array($v)) {
                            return $v[app()->getLocale()] ?? reset($v) ?: null;
                        }

                        return $v;
                    })
                    ->toggleable(),

                // İlçe
                TextColumn::make('district')
                    ->label(__('admin.field.district'))
                    ->getStateUsing(function ($record) {
                        $v = $record->location?->parent?->name;   // string veya array olabilir
                        if (is_array($v)) {
                            return $v[app()->getLocale()] ?? reset($v) ?: null;
                        }

                        return $v; // string
                    })
                    ->toggleable(),

                // İl
                TextColumn::make('province')
                    ->label(__('admin.field.province'))
                    ->getStateUsing(function ($record) {
                        $v = $record->location?->parent?->parent?->name;
                        if (is_array($v)) {
                            return $v[app()->getLocale()] ?? reset($v) ?: null;
                        }

                        return $v;
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ->recordActions([])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
