<?php

namespace App\Filament\Resources\BeachTypes\Pages;

use App\Filament\Resources\BeachTypes\BeachTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBeachType extends EditRecord
{
    protected static string $resource = BeachTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
