<?php

namespace App\Filament\Resources\Rooms;

use App\Filament\Resources\Rooms\Pages\CreateRoom;
use App\Filament\Resources\Rooms\Pages\EditRoom;
use App\Filament\Resources\Rooms\Pages\ListRooms;
use App\Filament\Resources\Rooms\Schemas\RoomForm;
use App\Filament\Resources\Rooms\Tables\RoomsTable;
use App\Models\Room;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $recordTitleAttribute = 'name_l';

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.hotel_group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.rooms.plural');
    }

    public static function getModelLabel(): string
    {
        return __('admin.rooms.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.rooms.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return RoomForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RoomsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRooms::route('/'),
            'create' => CreateRoom::route('/create'),
            'edit' => EditRoom::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getModel()::query();
    }

    // SADECE burada eager-load:
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['hotel', 'viewType']);
    }
}
