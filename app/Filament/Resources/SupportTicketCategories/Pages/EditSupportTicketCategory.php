<?php

namespace App\Filament\Resources\SupportTicketCategories\Pages;

use App\Filament\Resources\SupportTicketCategories\SupportTicketCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSupportTicketCategory extends EditRecord
{
    protected static string $resource = SupportTicketCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
