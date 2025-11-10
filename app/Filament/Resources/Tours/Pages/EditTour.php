<?php

namespace App\Filament\Resources\Tours\Pages;

use App\Filament\Resources\Tours\TourResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTour extends EditRecord
{
    protected static string $resource = TourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Kayıt açılırken slug_ui’yi doldur
        $data['slug_ui'] = $this->record->slug ?? [];
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['slug_ui'])) {
            $data['slug'] = $data['slug_ui'];
            unset($data['slug_ui']);
        }
        return $data;
    }
}
