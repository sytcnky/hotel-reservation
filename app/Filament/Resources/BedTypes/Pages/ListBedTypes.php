<?php

namespace App\Filament\Resources\BedTypes\Pages;

use App\Filament\Resources\BedTypes\BedTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBedTypes extends ListRecords
{
    protected static string $resource = BedTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
