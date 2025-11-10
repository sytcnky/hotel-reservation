<?php

namespace App\Filament\Resources\CancellationPolicies\Pages;

use App\Filament\Resources\CancellationPolicies\CancellationPolicyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditCancellationPolicy extends EditRecord
{
    protected static string $resource = CancellationPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
