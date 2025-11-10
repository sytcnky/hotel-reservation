<?php

namespace App\Filament\Resources\ViewTypes\Pages;

use App\Filament\Resources\ViewTypes\ViewTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditViewType extends EditRecord
{
    protected static string $resource = ViewTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
