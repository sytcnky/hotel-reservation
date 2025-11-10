<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource\RoleResource;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
    }
}
