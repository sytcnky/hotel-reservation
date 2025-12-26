<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return __('admin.orders.order_details');
    }

    public function getBreadcrumb(): string
    {
        return __('admin.orders.breadcrumb_details');
    }

    protected function getHeaderActions(): array
    {
        return [
            RestoreAction::make(),
        ];
    }
}
