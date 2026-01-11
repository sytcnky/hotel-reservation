<?php

namespace App\Filament\Resources\Currencies\Schemas;

use App\Support\Helpers\LocaleHelper;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        $locales = LocaleHelper::active();

        return $schema->schema([
            Tabs::make('i18n')
                ->tabs(
                    collect($locales)->map(function (string $loc) {
                        return Tab::make(strtoupper($loc))
                            ->schema([
                                TextInput::make("name.$loc")
                                    ->label(__('admin.field.name'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(debounce: 400)
                                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old) use ($loc) {
                                        $currentSlug = (string) ($get("slug.$loc") ?? '');
                                        $oldSlugFromName = Str::slug((string) ($old ?? ''));

                                        if ($currentSlug === '' || $currentSlug === $oldSlugFromName) {
                                            $set("slug.$loc", Str::slug((string) ($state ?? '')));
                                        }
                                    }),

                                TextInput::make("slug.$loc")
                                    ->label(__('admin.field.slug'))
                                    ->required()
                                    ->maxLength(255)
                                    ->live(debounce: 300)
                                    ->afterStateUpdated(function (Set $set, ?string $state) use ($loc) {
                                        $set("slug.$loc", Str::slug((string) ($state ?? '')));
                                    })
                                    ->dehydrateStateUsing(fn ($state) => Str::slug((string) ($state ?? ''))),

                                Textarea::make("description.$loc")
                                    ->label(__('admin.field.description'))
                                    ->rows(3),
                            ]);
                    })->all()
                )
                ->columnSpanFull(),

            TextInput::make('code')
                ->label('ISO Code')
                ->required()
                ->maxLength(3)
                ->rules(['alpha', 'uppercase'])
                ->helperText('ISO 4217, örn: TRY, EUR, USD'),

            TextInput::make('symbol')
                ->label('Symbol')
                ->maxLength(8)
                ->helperText('Örn: ₺, €, $, £'),

            TextInput::make('exponent')
                ->label('Minor units')
                ->numeric()
                ->default(2)
                ->helperText('Kuruş hanesi. Örn: JPY=0, KWD=3'),

            Toggle::make('is_active')
                ->label(__('admin.field.is_active'))
                ->default(true),

            TextInput::make('sort_order')
                ->label(__('admin.field.sort_order'))
                ->numeric()
                ->default(0),
        ]);
    }
}
