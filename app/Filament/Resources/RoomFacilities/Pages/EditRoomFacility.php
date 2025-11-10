<?php

namespace App\Filament\Resources\RoomFacilities\Pages;

use App\Filament\Resources\RoomFacilities\RoomFacilityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRoomFacility extends EditRecord
{
    protected static string $resource = RoomFacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
