<?php

namespace App\Filament\Resources\TravelGuides\Pages;

use App\Filament\Resources\TravelGuides\TravelGuideResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTravelGuide extends EditRecord
{
    protected static string $resource = TravelGuideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
