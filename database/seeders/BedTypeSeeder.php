<?php

namespace Database\Seeders;

use App\Models\BedType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BedTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['tr' => 'Tek Kişilik Yatak', 'en' => 'Single Bed'],
            ['tr' => 'Çift Kişilik Yatak', 'en' => 'Double Bed'],
            ['tr' => 'Queen Yatak', 'en' => 'Queen Bed'],
            ['tr' => 'King Yatak', 'en' => 'King Bed'],
            ['tr' => 'İkiz Yatak (Twin)', 'en' => 'Twin Bed'],
            ['tr' => 'Kanepe / Çekyat', 'en' => 'Sofa Bed'],
            ['tr' => 'Ranza', 'en' => 'Bunk Bed'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => Str::slug($name['tr']),
                'en' => Str::slug($name['en']),
            ];

            BedType::updateOrCreate(
                ['slug->tr' => $slug['tr']],
                [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => [],
                    'is_active' => true,
                    'sort_order' => $i + 1,
                ]
            );
        }
    }
}
