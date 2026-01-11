<?php

namespace App\Filament\Resources\Hotels\Pages;

use App\Filament\Resources\Hotels\HotelResource;
use App\Support\Helpers\LocaleHelper;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditHotel extends EditRecord
{
    protected static string $resource = HotelResource::class;

    /**
     * mutateFormDataBeforeFill artık gerekmiyor.
     * Çünkü form alanları doğrudan "slug.$loc" ile DB'deki "slug" jsonb alanına map ediliyor.
     */
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

            if ($val === null) {
                continue;
            }

            $val = trim((string) $val);

            if ($val === '') {
                continue;
            }

            $normalized[$loc] = Str::slug($val);
        }

        $data['slug'] = $normalized;

        return $data;
    }
}
