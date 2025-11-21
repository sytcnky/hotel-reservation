<?php

namespace App\Filament\Resources\VillaAmenities\Tables;

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

class VillaAmenitiesTable
{
    public static function configure(Table $table): Table
    {
        $localeOptions = collect(config('app.supported_locales', ['en','tr']))
            ->mapWithKeys(fn ($l) => [$l => strtoupper($l)])->toArray();

        return $table
            ->columns([
                TextColumn::make('name_l')->label(__('admin.field.name'))->sortable()->searchable(),
                TextColumn::make('slug_l')->label(__('admin.field.slug'))->sortable()->searchable(),
                IconColumn::make('is_active')->label(__('admin.field.is_active'))->boolean(),
                TextColumn::make('sort_order')->label(__('admin.field.sort_order'))->numeric()->sortable(),
                TextColumn::make('created_at')->label(__('admin.field.created_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label(__('admin.field.updated_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')->label(__('admin.field.deleted_at'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                Filter::make('display_locale')
                    ->label(__('admin.filter.display_locale'))
                    ->form([
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
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
