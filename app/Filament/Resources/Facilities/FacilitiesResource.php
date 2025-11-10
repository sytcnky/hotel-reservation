<?php

namespace App\Filament\Resources\Facilities;

use App\Filament\Resources\Facilities\Pages\CreateFacilities;
use App\Filament\Resources\Facilities\Pages\EditFacilities;
use App\Filament\Resources\Facilities\Pages\ListFacilities;
use App\Filament\Resources\Facilities\Schemas\FacilitiesForm;
use App\Filament\Resources\Facilities\Tables\FacilitiesTable;
use App\Models\Facility;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FacilitiesResource extends Resource
{
    protected static ?string $model = Facility::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.facility.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.facility.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.facility.plural'); }

    public static function getNavigationBadge(): ?string
    {
        try {
            return (string) static::getModel()::query()->count();
        } catch (\Throwable) {
            return null;
        }
    }

    /** canonical (v4) */
    public static function form(Schema $schema): Schema
    {
        return FacilitiesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FacilitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFacilities::route('/'),
            'create' => CreateFacilities::route('/create'),
            'edit' => EditFacilities::route('/{record}/edit'),
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
