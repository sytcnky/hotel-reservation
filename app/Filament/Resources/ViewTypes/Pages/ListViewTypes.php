<?php

namespace App\Filament\Resources\ViewTypes\Pages;

use App\Filament\Resources\ViewTypes\ViewTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListViewTypes extends ListRecords
{
    protected static string $resource = ViewTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
