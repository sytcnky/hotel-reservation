<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasAnyRole(['admin', 'editor']), 403);
    }
}
