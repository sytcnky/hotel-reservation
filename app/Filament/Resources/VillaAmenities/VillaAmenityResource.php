<?php

namespace App\Filament\Resources\VillaAmenities;

use App\Filament\Resources\VillaAmenities\Pages\CreateVillaAmenity;
use App\Filament\Resources\VillaAmenities\Pages\EditVillaAmenity;
use App\Filament\Resources\VillaAmenities\Pages\ListVillaAmenities;
use App\Filament\Resources\VillaAmenities\Schemas\VillaAmenityForm;
use App\Filament\Resources\VillaAmenities\Tables\VillaAmenitiesTable;
use App\Models\VillaAmenity;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VillaAmenityResource extends Resource
{
    protected static ?string $model = VillaAmenity::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.villa_amenity.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.villa_amenity.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.villa_amenity.plural'); }
    public static function getNavigationSort(): ?int { return 150; }

    public static function getPages(): array
    {
        return [
            'index'  => ListVillaAmenities::route('/'),
            'create' => CreateVillaAmenity::route('/create'),
            'edit'   => EditVillaAmenity::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return VillaAmenityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VillaAmenitiesTable::configure($table);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
