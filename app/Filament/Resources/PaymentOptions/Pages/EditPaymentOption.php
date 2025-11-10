<?php

namespace App\Filament\Resources\PaymentOptions\Pages;

use App\Filament\Resources\PaymentOptions\PaymentOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentOption extends EditRecord
{
    protected static string $resource = PaymentOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
