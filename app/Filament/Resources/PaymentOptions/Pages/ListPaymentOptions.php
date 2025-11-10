<?php

namespace App\Filament\Resources\PaymentOptions\Pages;

use App\Filament\Resources\PaymentOptions\PaymentOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentOptions extends ListRecords
{
    protected static string $resource = PaymentOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
