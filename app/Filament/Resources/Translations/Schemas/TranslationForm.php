<?php

namespace App\Filament\Resources\Translations\Schemas;

use App\Models\Language;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TranslationForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter(fn ($v) => is_string($v) && trim($v) !== '')
            ->map(fn ($v) => strtolower(trim($v)))
            ->values()
            ->all();

        return $schema->components([
            Section::make(__('admin.translations.fields.group'))
                ->schema([
                    TextInput::make('group')
                        ->label(__('admin.translations.fields.group'))
                        ->required()
                        ->maxLength(100),

                    TextInput::make('key')
                        ->label(__('admin.translations.fields.key'))
                        ->required()
                        ->maxLength(150)
                        ->unique(
                            table: 'translations',
                            column: 'key',
                            ignoreRecord: true,
                            modifyRuleUsing: function ($rule, callable $get) {
                                return $rule->where('group', $get('group'));
                            },
                        ),
                ]),

            Section::make(__('admin.translations.fields.values'))
                ->schema(
                    collect($locales)
                        ->map(fn (string $loc) =>
                        TextInput::make("values.{$loc}")
                            ->label(strtoupper($loc))
                            ->maxLength(1000)
                        )
                        ->all()
                ),
        ]);
    }
}
