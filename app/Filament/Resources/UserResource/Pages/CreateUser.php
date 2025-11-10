<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function authorizeAccess(): void
    {
        abort_unless(auth()->user()?->hasRole('admin'), 403);
    }
}
