<?php

namespace App\Filament\Resources\Translations\Tables;

use App\Models\Language;
use App\Models\Translation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Filters\SelectFilter;

class TranslationsTable
{
    public static function configure(Table $table): Table
    {
        // Aktif diller
        $locales = Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->all();

        // Grup & Anahtar inline editable
        $columns = [
            TextInputColumn::make('group')
                ->label('Grup')
                ->rules(['required', 'string', 'max:64'])
                ->searchable(),


            TextInputColumn::make('key')
                ->label('Anahtar')
                ->rules(['required', 'string', 'max:128'])
                ->searchable(),
        ];

        // Her dil için: values.tr, values.en ... => Filament nested state'i kendi handle etsin
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
                    ->label('Grup')
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
                    ->label('Çeviri Oluştur')
                    ->icon('heroicon-m-plus')
                    ->modalHeading('Yeni Çeviri')
                    ->modalWidth('lg')
                    ->form(function () use ($locales): array {
                        $fields = [
                            Forms\Components\TextInput::make('group')
                                ->label('Grup')
                                ->required()
                                ->maxLength(64),
                            Forms\Components\TextInput::make('key')
                                ->label('Anahtar')
                                ->required()
                                ->maxLength(128),
                        ];

                        foreach ($locales as $code) {
                            $fields[] = Forms\Components\TextInput::make("values.{$code}")
                                ->label(strtoupper($code));
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
