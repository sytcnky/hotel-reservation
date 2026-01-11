<?php

namespace App\Filament\Resources\VillaCategories;

use App\Filament\Resources\VillaCategories\Pages\CreateVillaCategory;
use App\Filament\Resources\VillaCategories\Pages\EditVillaCategory;
use App\Filament\Resources\VillaCategories\Pages\ListVillaCategories;
use App\Filament\Resources\VillaCategories\Schemas\VillaCategoryForm;
use App\Filament\Resources\VillaCategories\Tables\VillaCategoriesTable;
use App\Models\VillaCategory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VillaCategoryResource extends Resource
{
    protected static ?string $model = VillaCategory::class;

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.taxonomies');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.ent.villa_category.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.ent.villa_category.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.ent.villa_category.plural');
    }

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-rectangle-stack';
    }

    public static function getNavigationSort(): ?int
    {
        return 40;
    }

    /** Schema API (Filament v4) */
    public static function form(Schema $schema): Schema
    {
        return VillaCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VillaCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListVillaCategories::route('/'),
            'create' => CreateVillaCategory::route('/create'),
            'edit'   => EditVillaCategory::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
