<?php

namespace App\Filament\Resources\HotelThemes\Pages;

use App\Filament\Resources\HotelThemes\HotelThemeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditHotelTheme extends EditRecord
{
    protected static string $resource = HotelThemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
