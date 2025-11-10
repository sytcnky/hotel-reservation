<?php

namespace App\Filament\Resources\BoardTypes\Pages;

use App\Filament\Resources\BoardTypes\BoardTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBoardTypes extends ListRecords
{
    protected static string $resource = BoardTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
