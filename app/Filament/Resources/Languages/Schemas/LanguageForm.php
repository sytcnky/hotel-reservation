<?php

namespace App\Filament\Resources\Languages\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LanguageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Group::make()
                ->columnSpanFull()
                ->schema([
                    Grid::make()
                        ->columns(['default' => 1, 'lg' => 12])
                        ->gap(6)
                        ->schema([
                            // SOL (8)
                            Group::make()
                                ->columnSpan(['default' => 12, 'lg' => 8])
                                ->schema([
                                    Section::make(__('admin.languages.sections.general'))
                                        ->columns(2)
                                        ->schema([
                                            TextInput::make('code')
                                                ->label(__('admin.languages.form.code'))
                                                ->required()
                                                ->maxLength(8)
                                                ->rules(['alpha_dash'])
                                                ->helperText(__('admin.languages.form.code_help'))
                                                ->columnSpan(1),

                                            TextInput::make('locale')
                                                ->label(__('admin.languages.form.locale'))
                                                ->maxLength(16)
                                                ->helperText(__('admin.languages.form.locale_help'))
                                                ->columnSpan(1),

                                            TextInput::make('name')
                                                ->label(__('admin.languages.form.name'))
                                                ->required()
                                                ->maxLength(64)
                                                ->columnSpan(1),

                                            TextInput::make('native_name')
                                                ->label(__('admin.languages.form.native_name'))
                                                ->maxLength(64)
                                                ->columnSpan(1),
                                        ]),

                                    Section::make(__('admin.languages.sections.flag'))
                                        ->schema([
                                            FileUpload::make('flag')
                                                ->label(__('admin.languages.form.flag'))
                                                ->image()
                                                ->disk('public')
                                                ->directory('flags')
                                                ->visibility('public')
                                                ->imageEditor()
                                                ->maxSize(1024),
                                        ]),
                                ]),

                            // SAĞ (4)
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
