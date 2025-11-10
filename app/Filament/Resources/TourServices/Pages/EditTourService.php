<?php

namespace App\Filament\Resources\TourServices\Pages;

use App\Filament\Resources\TourServices\TourServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTourService extends EditRecord
{
    protected static string $resource = TourServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
