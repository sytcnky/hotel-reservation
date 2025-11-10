<?php

namespace App\Filament\Resources\BeachTypes\Pages;

use App\Filament\Resources\BeachTypes\BeachTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBeachTypes extends ListRecords
{
    protected static string $resource = BeachTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
