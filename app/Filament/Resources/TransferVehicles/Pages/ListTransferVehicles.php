<?php

namespace App\Filament\Resources\TransferVehicles\Pages;

use App\Filament\Resources\TransferVehicles\TransferVehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransferVehicles extends ListRecords
{
    protected static string $resource = TransferVehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
