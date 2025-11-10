<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected function afterCreate(): void
    {
        if (method_exists($this->record, 'syncRoles')) {
            $this->record->syncRoles(['customer']);
        }
    }
}
