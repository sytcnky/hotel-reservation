<?php

namespace App\Filament\Resources\Villas\Pages;

use App\Filament\Resources\Villas\VillaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVillas extends ListRecords
{
    protected static string $resource = VillaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
