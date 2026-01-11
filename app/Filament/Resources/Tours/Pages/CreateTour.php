<?php

namespace App\Filament\Resources\Tours\Pages;

use App\Filament\Resources\Tours\TourResource;
use App\Support\Helpers\LocaleHelper;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateTour extends CreateRecord
{
    protected static string $resource = TourResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
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
