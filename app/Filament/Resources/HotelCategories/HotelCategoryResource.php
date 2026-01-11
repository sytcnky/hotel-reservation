<?php

namespace App\Filament\Resources\HotelCategories;

use App\Filament\Resources\HotelCategories\Pages\CreateHotelCategory;
use App\Filament\Resources\HotelCategories\Pages\EditHotelCategory;
use App\Filament\Resources\HotelCategories\Pages\ListHotelCategories;
use App\Filament\Resources\HotelCategories\Schemas\HotelCategoryForm;
use App\Filament\Resources\HotelCategories\Tables\HotelCategoriesTable;
use App\Models\HotelCategory;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Throwable;

class HotelCategoryResource extends Resource
{
    protected static ?string $model = HotelCategory::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.hotel_category.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.hotel_category.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.hotel_category.plural'); }

    public static function getNavigationBadge(): ?string
    {
        try {
            return (string) static::getModel()::query()->count();
        } catch (Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public static function form(Schema $schema): Schema
    {
        return HotelCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotelCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListHotelCategories::route('/'),
            'create' => CreateHotelCategory::route('/create'),
            'edit'   => EditHotelCategory::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
