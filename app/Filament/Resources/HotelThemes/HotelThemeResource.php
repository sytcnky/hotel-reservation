<?php

namespace App\Filament\Resources\HotelThemes;

use App\Filament\Resources\HotelThemes\Pages\CreateHotelTheme;
use App\Filament\Resources\HotelThemes\Pages\EditHotelTheme;
use App\Filament\Resources\HotelThemes\Pages\ListHotelThemes;
use App\Filament\Resources\HotelThemes\Schemas\HotelThemeForm;
use App\Filament\Resources\HotelThemes\Tables\HotelThemesTable;
use App\Models\HotelTheme;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HotelThemeResource extends Resource
{
    protected static ?string $model = HotelTheme::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.hotel_theme.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.hotel_theme.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.hotel_theme.plural'); }

    public static function getNavigationBadge(): ?string
    {
        try {
            return (string) static::getModel()::query()->count();
        } catch (\Throwable) {
            return null;
        }
    }

    public static function form(Schema $schema): Schema
    {
        return HotelThemeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HotelThemesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHotelThemes::route('/'),
            'create' => CreateHotelTheme::route('/create'),
            'edit' => EditHotelTheme::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        $query = static::getModel()::query();

        if (in_array(SoftDeletingScope::class, class_uses_recursive(static::$model))) {
            $query->withoutGlobalScopes([SoftDeletingScope::class]);
        }

        return $query;
    }
}
