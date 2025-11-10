<?php

namespace App\Filament\Resources\BoardTypes\Pages;

use App\Filament\Resources\BoardTypes\BoardTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBoardType extends CreateRecord
{
    protected static string $resource = BoardTypeResource::class;
}
