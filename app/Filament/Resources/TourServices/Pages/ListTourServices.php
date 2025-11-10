<?php

namespace App\Filament\Resources\TourServices\Pages;

use App\Filament\Resources\TourServices\TourServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTourServices extends ListRecords
{
    protected static string $resource = TourServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
