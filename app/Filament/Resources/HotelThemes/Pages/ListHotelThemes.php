<?php

namespace App\Filament\Resources\HotelThemes\Pages;

use App\Filament\Resources\HotelThemes\HotelThemeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHotelThemes extends ListRecords
{
    protected static string $resource = HotelThemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
