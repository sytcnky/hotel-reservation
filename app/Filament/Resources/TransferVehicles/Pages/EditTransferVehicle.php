<?php

namespace App\Filament\Resources\TransferVehicles\Pages;

use App\Filament\Resources\TransferVehicles\TransferVehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransferVehicle extends EditRecord
{
    protected static string $resource = TransferVehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
