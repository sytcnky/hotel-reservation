<?php

namespace Database\Seeders;

use App\Models\VillaCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VillaCategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['tr' => 'Bahçeli', 'en' => 'With Garden'],
            ['tr' => 'Havuzlu', 'en' => 'With Pool'],
            ['tr' => 'Korunaklı', 'en' => 'Secluded'],
            ['tr' => 'Evcil Hayvan Dostu', 'en' => 'Pet Friendly'],
            ['tr' => 'Lüks', 'en' => 'Luxury'],
            ['tr' => 'Konsept', 'en' => 'Concept'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => Str::slug($name['tr']),
                'en' => Str::slug($name['en']),
            ];

            $existing = VillaCategory::query()
                ->whereRaw("slug->>'tr' = ?", [$slug['tr']])
                ->orWhereRaw("slug->>'en' = ?", [$slug['en']])
                ->first();

            if ($existing) {
                $existing->fill([
                    'name'        => $name,
                    'slug'        => $slug,
                    'description' => $existing->description ?? [],
                    'is_active'   => true,
                    'sort_order'  => $i + 1,
                ])->save();
            } else {
                VillaCategory::create([
                    'name'        => $name,
                    'slug'        => $slug,
                    'description' => [],
                    'is_active'   => true,
                    'sort_order'  => $i + 1,
                ]);
            }
        }
    }
}
