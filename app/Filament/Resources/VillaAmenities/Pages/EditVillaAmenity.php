<?php

namespace App\Filament\Resources\VillaAmenities\Pages;

use App\Filament\Resources\VillaAmenities\VillaAmenityResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditVillaAmenity extends EditRecord
{
    protected static string $resource = VillaAmenityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
