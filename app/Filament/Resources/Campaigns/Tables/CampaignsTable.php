<?php

namespace App\Filament\Resources\Campaigns\Tables;

use App\Models\Campaign;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CampaignsTable
{
    public static function configure(Table $table): Table
    {
        $base = config('app.locale', 'tr');
        $ui   = app()->getLocale();

        return $table
            ->columns([
                IconColumn::make('is_active')
                    ->label(__('admin.campaigns.table.active'))
                    ->boolean()
                    ->sortable(),

                TextColumn::make('title')
                    ->label(__('admin.campaigns.table.title'))
                    ->getStateUsing(function (Campaign $record) use ($ui, $base) {
                        return self::resolveLocalizedTitle($record->content ?? [], $ui, $base);
                    })
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('start_date')
                    ->label(__('admin.campaigns.table.start_date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label(__('admin.campaigns.table.end_date'))
                    ->date()
                    ->sortable(),

                TextColumn::make('priority')
                    ->label(__('admin.campaigns.table.priority'))
                    ->numeric()
                    ->sortable(),

                IconColumn::make('visible_on_web')
                    ->label('Web')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('visible_on_mobile')
                    ->label('Mobil')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('placements')
                    ->label(__('admin.campaigns.table.placements'))
                    ->getStateUsing(function (Campaign $record) {
                        return self::formatPlacementsSummary($record->placements ?? []);
                    })
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label(__('admin.campaigns.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('admin.campaigns.table.filter_active'))
                    ->boolean(),

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

    /**
     * content[locale]['title'] yapısından locale'e göre başlığı çözer.
     */
    protected static function resolveLocalizedTitle(mixed $content, string $ui, string $base): ?string
    {
        if (! is_array($content) || $content === []) {
            return null;
        }

        // Önce UI locale
        if (isset($content[$ui]['title']) && $content[$ui]['title'] !== '') {
            return (string) $content[$ui]['title'];
        }

        // Sonra base locale
        if (isset($content[$base]['title']) && $content[$base]['title'] !== '') {
            return (string) $content[$base]['title'];
        }

        // Son çare: ilk locale'in title'ı
        foreach ($content as $localeData) {
            if (is_array($localeData) && isset($localeData['title']) && $localeData['title'] !== '') {
                return (string) $localeData['title'];
            }
        }

        return null;
    }

    /**
     * placements array'ini, çevirilmiş etiketlerle virgüllü string'e çevirir.
     */
    protected static function formatPlacementsSummary(mixed $placements): ?string
    {
        if (! is_array($placements) || $placements === []) {
            return null;
        }

        $labels = [];

        foreach ($placements as $key) {
            $translationKey = "admin.campaigns.placements.$key";
            $label          = __($translationKey);

            // Çeviri yoksa anahtarı olduğu gibi gösterme, sadece key'i kullan.
            if ($label === $translationKey) {
                $label = (string) $key;
            }

            $labels[] = $label;
        }

        return implode(', ', $labels);
    }
}
