<?php

namespace App\Filament\Resources\TransferRoutes\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\Filter;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select as FormSelect;
use Illuminate\Support\Facades\Session;

class TransferRoutesTable
{
    public static function configure(Table $table): Table
    {
        $localeOptions = collect(config('app.supported_locales', ['tr','en']))
            ->mapWithKeys(fn ($l) => [$l => strtoupper($l)])
            ->toArray();

        $nameFromI18n = function ($v): ?string {
            $loc = Session::get('display_locale') ?: app()->getLocale();
            $base = config('app.locale', 'tr');

            if (is_array($v)) {
                return $v[$loc] ?? $v[$base] ?? (string) (array_values($v)[0] ?? null);
            }
            return $v ? (string) $v : null;
        };

        return $table
            ->columns([
                TextColumn::make('from_location')
                    ->label('Nereden')
                    ->getStateUsing(fn ($record) => $nameFromI18n($record->from?->name)),

                TextColumn::make('to_location')
                    ->label('Nereye')
                    ->getStateUsing(fn ($record) => $nameFromI18n($record->to?->name)),

                TextColumn::make('duration_minutes')->label('Süre (dk)')->sortable(),
                TextColumn::make('distance_km')->label('Km')->sortable(),
                IconColumn::make('is_active')->label('Aktif')->boolean(),
                TextColumn::make('sort_order')->label('Sıra')->numeric()->sortable(),

                TextColumn::make('created_at')->label('Oluşturma')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Güncelleme')->dateTime()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')->label('Silinmiş')->dateTime()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),

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
                        if (!empty($data['value'])) {
                            Session::put('display_locale', (string) $data['value']);
                        }
                        return $query;
                    }),
            ])
            ->recordActions([ EditAction::make() ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
