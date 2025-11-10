<?php

namespace App\Filament\Resources\ViewTypes\Pages;

use App\Filament\Resources\ViewTypes\ViewTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateViewType extends CreateRecord
{
    protected static string $resource = ViewTypeResource::class;
}
