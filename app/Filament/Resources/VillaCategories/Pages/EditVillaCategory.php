<?php

namespace App\Filament\Resources\VillaCategories\Pages;

use App\Filament\Resources\VillaCategories\VillaCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditVillaCategory extends EditRecord
{
    protected static string $resource = VillaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
