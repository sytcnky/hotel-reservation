<?php

namespace App\Filament\Resources\PaymentOptions\Pages;

use App\Filament\Resources\PaymentOptions\PaymentOptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentOption extends CreateRecord
{
    protected static string $resource = PaymentOptionResource::class;
}
