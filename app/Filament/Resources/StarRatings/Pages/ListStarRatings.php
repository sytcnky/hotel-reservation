<?php

namespace App\Filament\Resources\StarRatings\Pages;

use App\Filament\Resources\StarRatings\StarRatingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStarRatings extends ListRecords
{
    protected static string $resource = StarRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
