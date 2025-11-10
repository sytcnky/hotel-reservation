<?php

namespace App\Filament\Resources\Translations;

use App\Filament\Resources\Translations\Pages\CreateTranslation;
use App\Filament\Resources\Translations\Pages\EditTranslation;
use App\Filament\Resources\Translations\Pages\ListTranslations;
use App\Filament\Resources\Translations\Schemas\TranslationForm;
use App\Filament\Resources\Translations\Tables\TranslationsTable;
use App\Models\Translation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TranslationResource extends Resource
{
    protected static ?string $model = Translation::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-language';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.settings_group');
    }

    public static function getNavigationLabel(): string
    {
        return 'Çeviriler';
    }

    public static function getModelLabel(): string
    {
        return 'Çeviri';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Çeviriler';
    }

    public static function form(Schema $schema): Schema
    {
        return TranslationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\Translations\Tables\TranslationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTranslations::route('/'),
            'create' => CreateTranslation::route('/create'),
            'edit'   => EditTranslation::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('group')
            ->orderBy('key');
    }
}
