<?php

namespace App\Filament\Resources\Translations\Schemas;

use App\Support\Helpers\LocaleHelper;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TranslationForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = LocaleHelper::active();

        return $schema->components([
            Section::make('Genel')
                ->schema([
                    TextInput::make('group')
                        ->label('Grup')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('key')
                        ->label('Anahtar')
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

            Section::make('Değerler')
                ->schema(
                    collect($locales)
                        ->map(fn (string $loc) =>
                        TextInput::make("values.{$loc}")
                            ->label(strtoupper($loc))
                            ->maxLength(500)
                        )
                        ->all()
                ),
        ]);
    }
}
