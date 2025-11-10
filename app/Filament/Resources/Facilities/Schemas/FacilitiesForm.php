<?php

namespace App\Filament\Resources\Facilities\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class FacilitiesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make('translations')
                ->tabs(
                    collect(config('app.supported_locales'))
                        ->map(function (string $locale) {
                            return Tab::make(strtoupper($locale))
                                ->schema([
                                    TextInput::make("name.$locale")
                                        ->label(__('admin.field.name'))
                                        ->required()
                                        ->maxLength(255)
                                        ->live(debounce: 400)
                                        ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?string $old) use ($locale) {
                                            $currentSlug = (string) ($get("slug.$locale") ?? '');
                                            $oldSlugFromName = Str::slug($old ?? '');
                                            if ($currentSlug === '' || $currentSlug === $oldSlugFromName) {
                                                $set("slug.$locale", Str::slug($state ?? ''));
                                            }
                                        }),

                                    TextInput::make("slug.$locale")
                                        ->label(__('admin.field.slug'))
                                        ->required()
                                        ->maxLength(255)
                                        ->live(debounce: 300)
                                        ->afterStateUpdated(function (Set $set, ?string $state) use ($locale) {
                                            $set("slug.$locale", Str::slug($state ?? ''));
                                        })
                                        ->dehydrateStateUsing(fn ($state) => Str::slug($state ?? '')),

                                    Textarea::make("description.$locale")
                                        ->label(__('admin.field.description'))
                                        ->rows(4),
                                ]);
                        })
                        ->toArray()
                )
                ->columnSpanFull(),

            Toggle::make('is_active')->label(__('admin.field.is_active'))->default(true),

            TextInput::make('sort_order')->label(__('admin.field.sort_order'))->numeric()->default(0),
        ]);
    }
}
