<?php

namespace App\Filament\Resources\Campaigns\Tables;

use App\Models\Campaign;
use App\Support\Date\DatePresenter;
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
        $uiLocale = app()->getLocale();

        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.campaigns.table.title'))
                    ->getStateUsing(function (Campaign $record) use ($uiLocale) {
                        return self::resolveLocalizedTitle($record->content ?? [], $uiLocale);
                    })
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('start_date')
                    ->label(__('admin.campaigns.table.start_date'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label(__('admin.campaigns.table.end_date'))
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
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
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('deleted_at')
                    ->formatStateUsing(fn ($state) => DatePresenter::humanDateTimeShort($state))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('priority')
                    ->label(__('admin.campaigns.table.priority'))
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label(__('admin.campaigns.table.active'))
                    ->boolean()
                    ->sortable(),
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
     * content[locale]['title'] içinden sadece UI locale title çözer.
     * Kontrat: fallback YOK.
     */
    protected static function resolveLocalizedTitle(mixed $content, string $uiLocale): ?string
    {
        if (! is_array($content) || $content === []) {
            return null;
        }

        $title = $content[$uiLocale]['title'] ?? null;

        return ($title !== null && $title !== '')
            ? (string) $title
            : null;
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

            if ($label === $translationKey) {
                $label = (string) $key;
            }

            $labels[] = $label;
        }

        return implode(', ', $labels);
    }
}
