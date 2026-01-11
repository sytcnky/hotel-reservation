<?php

namespace App\Filament\Resources\Facilities\Schemas;

use App\Support\Helpers\LocaleHelper;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
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
        $locales = LocaleHelper::active();

        return $schema->components([
            Group::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make()
                        ->columns(['default' => 1, 'lg' => 12])
                        ->gap(6)
                        ->schema([
                            // SOL (8) — i18n
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 8])
                                ->schema([
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
                                                            ->rows(4),
                                                    ]);
                                            })->all()
                                        ),
                                ]),

                            // SAĞ (4) — durum
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 4])
                                ->schema([
                                    Section::make(__('admin.field.status'))
                                        ->schema([
                                            Toggle::make('is_active')
                                                ->label(__('admin.field.is_active'))
                                                ->default(true),

                                            TextInput::make('sort_order')
                                                ->label(__('admin.field.sort_order'))
                                                ->numeric()
                                                ->default(0),
                                        ]),
                                ]),
                        ]),
                ]),
        ]);
    }
}
