<?php

namespace App\Filament\Resources\RoomFacilities\Pages;

use App\Filament\Resources\RoomFacilities\RoomFacilityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoomFacilities extends ListRecords
{
    protected static string $resource = RoomFacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
