<?php

namespace Database\Seeders;

use App\Models\StarRating;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StarRatingSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['tr' => '1 Yıldız', 'en' => '1 Star'],
            ['tr' => '2 Yıldız', 'en' => '2 Stars'],
            ['tr' => '3 Yıldız', 'en' => '3 Stars'],
            ['tr' => '4 Yıldız', 'en' => '4 Stars'],
            ['tr' => '5 Yıldız', 'en' => '5 Stars'],
            ['tr' => 'Sınıflandırılmamış', 'en' => 'Unrated'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => Str::slug($name['tr']),
                'en' => Str::slug($name['en']),
            ];

            $existing = StarRating::query()
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
                StarRating::create([
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
