<?php

namespace App\Filament\Resources\RoomFacilities;

use App\Filament\Resources\RoomFacilities\Pages\CreateRoomFacility;
use App\Filament\Resources\RoomFacilities\Pages\EditRoomFacility;
use App\Filament\Resources\RoomFacilities\Pages\ListRoomFacilities;
use App\Filament\Resources\RoomFacilities\Schemas\RoomFacilityForm;
use App\Filament\Resources\RoomFacilities\Tables\RoomFacilitiesTable;
use App\Models\RoomFacility;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomFacilityResource extends Resource
{
    protected static ?string $model = RoomFacility::class;

    public static function getNavigationGroup(): ?string { return __('admin.nav.taxonomies'); }
    public static function getNavigationLabel(): string { return __('admin.ent.room_facility.plural'); }
    public static function getModelLabel(): string { return __('admin.ent.room_facility.singular'); }
    public static function getPluralModelLabel(): string { return __('admin.ent.room_facility.plural'); }

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
        return RoomFacilityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomFacilitiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoomFacilities::route('/'),
            'create' => CreateRoomFacility::route('/create'),
            'edit' => EditRoomFacility::route('/{record}/edit'),
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
