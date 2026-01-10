<?php

namespace App\Filament\Resources\TravelGuides\Tables;

use App\Support\Helpers\I18nHelper;
use App\Support\Helpers\LocaleHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
        $uiLocale   = app()->getLocale();
        $baseLocale = LocaleHelper::defaultCode();

        $decodeJsonIfNeeded = static function (mixed $value): mixed {
            if (is_array($value)) {
                return $value;
            }

            if (is_string($value) && $value !== '') {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }

            return $value;
        };

        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.travel_guides.fields.title'))
                    ->state(function ($record) use ($uiLocale, $baseLocale, $decodeJsonIfNeeded): string {
                        // Kaynağı ham al (jsonb olabilir), gerekirse decode et.
                        $raw = $record->getRawOriginal('title') ?? $record->title;
                        $raw = $decodeJsonIfNeeded($raw);

                        return I18nHelper::scalar($raw, $uiLocale, $baseLocale) ?: '—';
                    })
                    ->searchable(),

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
