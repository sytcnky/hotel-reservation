<?php

namespace App\Filament\Resources\Tours\Pages;

use App\Filament\Resources\Tours\TourResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTour extends CreateRecord
{
    protected static string $resource = TourResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Yeni kayıtta boş gelebilir, form uyumu için ekle
        $data['slug_ui'] = $data['slug'] ?? [];
        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['slug_ui'])) {
            $data['slug'] = $data['slug_ui'];
            unset($data['slug_ui']);
        }
        return $data;
    }
}
