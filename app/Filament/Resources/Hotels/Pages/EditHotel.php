<?php

namespace App\Filament\Resources\Hotels\Pages;

use App\Filament\Resources\Hotels\HotelResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditHotel extends EditRecord
{
    protected static string $resource = HotelResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // DB slug -> UI slug
        $data['slug_ui'] = $data['slug'] ?? [];

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $base = config('app.locale', 'tr');
        $slug = [];

        // slug_ui her zaman DB'ye yazılır
        foreach ((array) ($data['slug_ui'] ?? []) as $loc => $uiValue) {
            $slug[$loc] = Str::slug((string) $uiValue);
        }

        $data['slug'] = $slug;
        unset($data['slug_ui']);

        $data['canonical_slug'] = Str::slug($slug[$base] ?? (reset($slug) ?: 'otel'));

        return $data;
    }
}
