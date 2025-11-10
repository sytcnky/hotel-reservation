<?php

namespace Database\Seeders;

use App\Models\RoomFacility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RoomFacilitySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['tr' => 'Televizyon',      'en' => 'Television'],
            ['tr' => 'Klima',           'en' => 'Air Conditioning'],
            ['tr' => 'Minibar',         'en' => 'Minibar'],
            ['tr' => 'Kasa',            'en' => 'Safe Box'],
            ['tr' => 'Saç Kurutma Makinesi', 'en' => 'Hair Dryer'],
            ['tr' => 'Çay/Kahve Makinesi', 'en' => 'Tea/Coffee Maker'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => Str::slug($name['tr']),
                'en' => Str::slug($name['en']),
            ];

            $existing = RoomFacility::query()
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
                RoomFacility::create([
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
