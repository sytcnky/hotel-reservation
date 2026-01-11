<?php

namespace App\Filament\Resources\Translations\Tables;

use App\Models\Language;
use App\Models\Translation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TranslationsTable
{
    public static function configure(Table $table): Table
    {
        // Aktif site dilleri (fallback yok)
        $locales = Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter(fn ($v) => is_string($v) && trim($v) !== '')
            ->map(fn ($v) => strtolower(trim($v)))
            ->values()
            ->all();

        // Grup & Anahtar inline editable
        $columns = [
            TextInputColumn::make('group')
                ->label(__('admin.translations.fields.group'))
                ->rules(['required', 'string', 'max:64'])
                ->searchable(),

            TextInputColumn::make('key')
                ->label(__('admin.translations.fields.key'))
                ->rules(['required', 'string', 'max:128'])
                ->searchable(),
        ];

        // Her dil için: values.tr, values.en ...
        foreach ($locales as $code) {
            $columns[] = TextInputColumn::make("values.{$code}")
                ->label(strtoupper($code))
                ->searchable()
                ->rules(['nullable', 'string', 'max:1000']);
        }

        return $table
            ->columns($columns)
            ->filters([
                SelectFilter::make('group')
                    ->label(__('admin.translations.filters.group'))
                    ->options(
                        Translation::query()
                            ->select('group')
                            ->distinct()
                            ->orderBy('group')
                            ->pluck('group', 'group')
                            ->toArray()
                    ),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                Action::make('create')
                    ->label(__('admin.translations.actions.create'))
                    ->icon('heroicon-m-plus')
                    ->modalHeading(__('admin.translations.modals.create_heading'))
                    ->modalWidth('lg')
                    ->form(function () use ($locales): array {
                        $fields = [
                            Forms\Components\TextInput::make('group')
                                ->label(__('admin.translations.fields.group'))
                                ->required()
                                ->maxLength(64),

                            Forms\Components\TextInput::make('key')
                                ->label(__('admin.translations.fields.key'))
                                ->required()
                                ->maxLength(128),
                        ];

                        foreach ($locales as $code) {
                            $fields[] = Forms\Components\TextInput::make("values.{$code}")
                                ->label(strtoupper($code))
                                ->maxLength(1000);
                        }

                        return $fields;
                    })
                    ->action(function (array $data): void {
                        Translation::create($data);
                    }),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
