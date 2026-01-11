<?php

namespace App\Filament\Resources\TourCategories;

use App\Filament\Resources\TourCategories\Pages\CreateTourCategory;
use App\Filament\Resources\TourCategories\Pages\EditTourCategory;
use App\Filament\Resources\TourCategories\Pages\ListTourCategories;
use App\Filament\Resources\TourCategories\Schemas\TourCategoryForm;
use App\Filament\Resources\TourCategories\Tables\TourCategoriesTable;
use App\Models\TourCategory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TourCategoryResource extends Resource
{
    protected static ?string $model = TourCategory::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.tour_category.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.tour_category.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.tour_category.plural'); }

    public static function getNavigationIcon(): ?string { return 'heroicon-o-tag'; }

    public static function getNavigationBadge(): ?string
    {
        try {
            return (string) static::getModel()::query()->count();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return TourCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TourCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTourCategories::route('/'),
            'create' => CreateTourCategory::route('/create'),
            'edit'   => EditTourCategory::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
