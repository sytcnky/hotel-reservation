<?php

namespace App\Filament\Resources\TravelGuides\Pages;

use App\Filament\Resources\TravelGuides\TravelGuideResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTravelGuides extends ListRecords
{
    protected static string $resource = TravelGuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
