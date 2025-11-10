<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['tr' => 'Ücretsiz Wi-Fi',     'en' => 'Free Wi-Fi'],
            ['tr' => 'Otopark',            'en' => 'Parking'],
            ['tr' => 'Açık Yüzme Havuzu',  'en' => 'Outdoor Pool'],
            ['tr' => 'Kapalı Yüzme Havuzu', 'en' => 'Indoor Pool'],
            ['tr' => 'Spa & Sauna',        'en' => 'Spa & Sauna'],
            ['tr' => 'Fitness Merkezi',    'en' => 'Gym'],
            ['tr' => 'Özel Plaj',          'en' => 'Private Beach'],
            ['tr' => 'Klima',              'en' => 'Air Conditioning'],
            ['tr' => 'Havaalanı Servisi',  'en' => 'Airport Shuttle'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => Str::slug($name['tr']),
                'en' => Str::slug($name['en']),
            ];

            $existing = Facility::query()
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
                Facility::create([
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
