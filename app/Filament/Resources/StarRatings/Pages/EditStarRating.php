<?php

namespace App\Filament\Resources\StarRatings\Pages;

use App\Filament\Resources\StarRatings\StarRatingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStarRating extends EditRecord
{
    protected static string $resource = StarRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
