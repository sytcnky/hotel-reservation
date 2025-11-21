<?php

namespace App\Filament\Resources\VillaAmenities\Pages;

use App\Filament\Resources\VillaAmenities\VillaAmenityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVillaAmenities extends ListRecords
{
    protected static string $resource = VillaAmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
