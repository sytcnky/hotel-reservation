<?php

namespace App\Filament\Resources\TravelGuides\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TravelGuidesTable
{
    public static function configure(Table $table): Table
    {
        $uiLocale = app()->getLocale();
        $base = config('app.locale', 'tr');
        $locales = config('app.supported_locales', [$base]);

        $toArray = function ($value): ?array {
            if (is_array($value)) {
                return $value;
            }

            if (is_string($value) && $value !== '') {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            }

            return null;
        };

        $pick = function ($value) use ($uiLocale, $locales, $toArray): string {
            $json = $toArray($value);

            if (! is_array($json)) {
                return '—';
            }

            $candidates = array_values(array_unique(array_filter(array_merge([$uiLocale], $locales))));
            foreach ($candidates as $loc) {
                $v = $json[$loc] ?? null;
                if (is_string($v) && trim($v) !== '') {
                    return trim($v);
                }
            }

            foreach ($json as $v) {
                if (is_string($v) && trim($v) !== '') {
                    return trim($v);
                }
            }

            return '—';
        };

        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.travel_guides.fields.title'))
                    ->state(function ($record) use ($pick) {
                        // sadece record üzerinden tek değer üret
                        return $pick($record->getRawOriginal('title') ?? $record->title);
                    })
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label(__('admin.field.is_active'))
                    ->boolean(),

                TextColumn::make('published_at')
                    ->label(__('admin.travel_guides.fields.published_at'))
                    ->dateTime()
                    ->sortable(),

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
