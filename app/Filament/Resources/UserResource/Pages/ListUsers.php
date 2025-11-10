<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        // Admin görsün; diğerleri butonu görmesin
        return [
            Actions\CreateAction::make()->visible(fn () => auth()->user()?->hasRole('admin')),
        ];
    }
}
