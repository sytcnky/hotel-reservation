<?php

namespace App\Filament\Resources\Hotels\Pages;

use App\Filament\Resources\Hotels\HotelResource;
use App\Support\Helpers\LocaleHelper;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateHotel extends CreateRecord
{
    protected static string $resource = HotelResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->normalizeSlugData($data);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->normalizeSlugData($data);
    }

    private function normalizeSlugData(array $data): array
    {
        $locales = LocaleHelper::active();
        $slug = (array) ($data['slug'] ?? []);
        $normalized = [];

        foreach ($locales as $loc) {
            $val = $slug[$loc] ?? null;

            if ($val === null || trim((string) $val) === '') {
                continue;
            }

            $normalized[$loc] = Str::slug((string) $val);
        }

        $data['slug'] = $normalized;

        unset($data['slug_ui'], $data['canonical_slug']);

        return $data;
    }
}
