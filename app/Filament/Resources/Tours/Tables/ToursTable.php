<?php

namespace App\Filament\Resources\Tours\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Session;

class ToursTable
{
    public static function configure(Table $table): Table
    {
        $localeOptions = collect(config('app.supported_locales', ['tr','en']))
            ->mapWithKeys(fn ($l) => [$l => strtoupper($l)])->toArray();

        return $table
            ->columns([
                TextColumn::make('name_i18n')
                    ->label(__('admin.field.name'))
                    ->getStateUsing(function ($record) {
                        $loc = Session::get('display_locale', app()->getLocale());
                        $v = $record->name;
                        if (is_array($v)) return $v[$loc] ?? reset($v) ?: null;
                        if (is_string($v) && str_starts_with($v, '{')) {
                            $d = json_decode($v, true); return $d[$loc] ?? reset($d) ?: null;
                        }
                        return $v;
                    })
                    ->sortable(
                        query: function (Builder $q, string $dir) {
                            $loc  = Session::get('display_locale', app()->getLocale());
                            $base = config('app.locale','tr');
                            return $q->orderByRaw("COALESCE(name->>?, name->>?) {$dir}", [$loc,$base]);
                        }
                    )
                    ->searchable(
                        query: function (Builder $q, string $search) {
                            $loc  = Session::get('display_locale', app()->getLocale());
                            $base = config('app.locale','tr');
                            $like = '%'.$search.'%';
                            return $q->whereRaw('(name->>? ILIKE ? OR name->>? ILIKE ?)', [$loc,$like,$base,$like]);
                        }
                    ),

                TextColumn::make('category_name')
                    ->label(__('admin.field.category'))
                    ->getStateUsing(function ($record) {
                        $loc = Session::get('display_locale', app()->getLocale());
                        $v = $record->category?->name ?? null;
                        if (is_array($v)) return $v[$loc] ?? reset($v) ?: null;
                        if (is_string($v) && str_starts_with($v, '{')) {
                            $d = json_decode($v, true); return $d[$loc] ?? reset($d) ?: null;
                        }
                        return $v;
                    })
                    ->toggleable(),

                TextColumn::make('duration')->label(__('admin.tours.form.duration'))->sortable()->toggleable(),
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
            ->toolbarActions([ BulkActionGroup::make([ DeleteBulkAction::make() ]) ]);
    }
}
