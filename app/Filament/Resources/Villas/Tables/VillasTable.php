<?php

namespace App\Filament\Resources\Villas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class VillasTable
{
    public static function configure(Table $table): Table
    {
        $localeOptions = collect(config('app.supported_locales', ['tr', 'en']))
            ->mapWithKeys(fn ($l) => [$l => strtoupper($l)])
            ->toArray();

        return $table
            ->columns([
                // Villa adı (JSON i18n)
                TextColumn::make('name_i18n')
                    ->label(__('admin.field.name'))
                    ->getStateUsing(function ($record) {
                        $v   = $record->name;
                        $loc = app()->getLocale();

                        if (is_array($v)) {
                            return $v[$loc] ?? reset($v) ?: null;
                        }

                        return $v;
                    })
                    ->sortable(
                        query: function (Builder $q, string $dir) {
                            $loc  = app()->getLocale();
                            $base = config('app.locale', 'tr');

                            return $q->orderByRaw(
                                "COALESCE(name->>?, name->>?) {$dir}",
                                [$loc, $base]
                            );
                        }
                    )
                    ->searchable(
                        query: function (Builder $q, string $search) {
                            $loc   = app()->getLocale();
                            $base  = config('app.locale', 'tr');
                            $like  = '%' . $search . '%';

                            return $q->whereRaw(
                                '(name->>? ILIKE ? OR name->>? ILIKE ?)',
                                [$loc, $like, $base, $like]
                            );
                        }
                    ),

                // Kategori
                TextColumn::make('category.name_l')
                    ->label(__('admin.field.category'))
                    ->sortable(
                        query: fn (Builder $q, string $dir) => $q
                            ->join('villa_categories as vc', 'vc.id', '=', 'villas.villa_category_id')
                            ->orderByLocalizedOn('vc.name', $dir)
                            ->select('villas.*')
                    )
                    ->searchable(
                        query: fn (Builder $q, string $search) =>
                        $q->whereRelationLocalizedLike('category', 'name', $search)
                    ),

                // Bölge (area)
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

                // İlçe (district)
                TextColumn::make('district')
                    ->label(__('admin.field.district'))
                    ->getStateUsing(function ($record) {
                        $v = $record->location?->parent?->name;
                        if (is_array($v)) {
                            return $v[app()->getLocale()] ?? reset($v) ?: null;
                        }

                        return $v;
                    })
                    ->toggleable(),

                // İl (province)
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

                // İptal politikası
                TextColumn::make('cancellationPolicy.name_l')
                    ->label(__('admin.villas.form.cancellation_policy'))
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

                // Görüntü dili seçimi
                Filter::make('display_locale')
                    ->label(__('admin.filter.display_locale'))
                    ->schema([
                        FormSelect::make('value')
                            ->label(__('admin.filter.display_locale'))
                            ->options($localeOptions)
                            ->default(Session::get('display_locale'))
                            ->live(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (! empty($data['value'])) {
                            Session::put('display_locale', (string) $data['value']);
                        }

                        return $query;
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
