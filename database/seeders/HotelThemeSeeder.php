<?php

namespace Database\Seeders;

use App\Models\HotelTheme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HotelThemeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['tr' => 'Balayı', 'en' => 'Honeymoon'],
            ['tr' => 'Aile Oteli', 'en' => 'Family Hotel'],
            ['tr' => 'Tatil Köyü', 'en' => 'Resort'],
            ['tr' => 'Butik Otel', 'en' => 'Boutique Hotel'],
            ['tr' => 'Şehir Oteli', 'en' => 'City Hotel'],
            ['tr' => 'Yetişkin Oteli', 'en' => 'Adults Only'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => Str::slug($name['tr']),
                'en' => Str::slug($name['en']),
            ];

            $existing = HotelTheme::query()
                ->where('slug->>tr', $slug['tr'])
                ->orWhere('slug->>en', $slug['en'])
                ->first();

            if ($existing) {
                $existing->fill([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $existing->description ?? [],
                    'is_active' => true,
                    'sort_order' => $i,
                ])->save();
            } else {
                HotelTheme::create([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => [],
                    'is_active' => true,
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
