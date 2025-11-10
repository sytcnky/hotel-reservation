<?php

namespace App\Filament\Resources\BoardTypes\Pages;

use App\Filament\Resources\BoardTypes\BoardTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBoardType extends EditRecord
{
    protected static string $resource = BoardTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
