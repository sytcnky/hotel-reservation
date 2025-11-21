<?php

namespace App\Filament\Resources\Villas\Pages;

use App\Filament\Resources\Villas\VillaResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateVilla extends CreateRecord
{
    protected static string $resource = VillaResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $base    = config('app.locale', 'tr');
        $locales = config('app.supported_locales', [$base]);

        // UI slug -> gerçek slug
        $slug = [];
        foreach ($locales as $loc) {
            $ui = $data['slug_ui'][$loc] ?? ($data['name'][$loc] ?? null);
            if ($ui !== null && $ui !== '') {
                $slug[$loc] = Str::slug((string) $ui);
            }
        }

        $data['slug'] = $slug;
        unset($data['slug_ui']);

        // canonical
        $data['canonical_slug'] = Str::slug($slug[$base] ?? (reset($slug) ?: 'villa'));

        return $data;
    }
}
