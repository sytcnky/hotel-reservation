<?php

namespace App\Filament\Resources\SupportTicketCategories\Pages;

use App\Filament\Resources\SupportTicketCategories\SupportTicketCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSupportTicketCategories extends ListRecords
{
    protected static string $resource = SupportTicketCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
