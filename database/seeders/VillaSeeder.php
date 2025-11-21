<?php

namespace Database\Seeders;

use App\Models\Villa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VillaSeeder extends Seeder
{
    public function run(): void
    {
        $villas = [
            [
                'name' => [
                    'tr' => 'Villa Luna',
                    'en' => 'Villa Luna',
                ],
                'slug' => [
                    'tr' => 'villa-luna',
                    'en' => 'villa-luna',
                ],
                'description' => [
                    'tr' => 'Doğa içinde huzurlu bir tatil villası.',
                    'en' => 'A peaceful holiday villa surrounded by nature.',
                ],
                'highlights' => [
                    'tr' => [
                        ['value' => 'Özel havuz'],
                        ['value' => 'Denize 600 metre'],
                    ],
                ],
                'stay_info' => [
                    'tr' => [
                        ['value' => 'Giriş saati 15:00'],
                        ['value' => 'Çıkış saati 11:00'],
                    ],
                ],
                'max_guests' => 6,
                'bedroom_count' => 3,
                'bathroom_count' => 2,
                'villa_category_id' => 1,
                'cancellation_policy_id' => 1,
                'location_id' => 1,
                'address_line' => 'Örnek Mah. 123. Sokak',
                'latitude' => 36.8000000,
                'longitude' => 28.2300000,
                'nearby' => [
                    [
                        'icon' => 'fi-ss-marker',
                        'label' => [
                            'tr' => 'Market',
                            'en' => 'Market'
                        ],
                        'distance' => [
                            'tr' => '200 m',
                            'en' => '200 m'
                        ],
                    ],
                ],
                'phone' => '+90 555 111 2233',
                'email' => 'info@villaluna.com',
                'promo_video_id' => 'abc123',
                'is_active' => true,
                'sort_order' => 0,
            ],

            [
                'name' => [
                    'tr' => 'Villa Mirage',
                    'en' => 'Villa Mirage',
                ],
                'slug' => [
                    'tr' => 'villa-mirage',
                    'en' => 'villa-mirage',
                ],
                'description' => [
                    'tr' => 'Panoramik deniz manzaralı lüks villa.',
                    'en' => 'Luxury villa with panoramic sea view.',
                ],
                'highlights' => [
                    'tr' => [
                        ['value' => 'Deniz manzarası'],
                        ['value' => 'Geniş teras'],
                    ],
                ],
                'stay_info' => [
                    'tr' => [
                        ['value' => 'Evcil hayvan kabul edilmez'],
                    ],
                ],
                'max_guests' => 8,
                'bedroom_count' => 4,
                'bathroom_count' => 3,
                'villa_category_id' => 1,
                'cancellation_policy_id' => 1,
                'location_id' => 1,
                'address_line' => 'Sahil Cad. No:45',
                'latitude' => 36.7991234,
                'longitude' => 28.2294567,
                'nearby' => [],
                'phone' => '+90 555 333 4455',
                'email' => 'info@villamirage.com',
                'promo_video_id' => null,
                'is_active' => true,
                'sort_order' => 1,
            ],

            [
                'name' => [
                    'tr' => 'Villa Stone',
                    'en' => 'Villa Stone',
                ],
                'slug' => [
                    'tr' => 'villa-stone',
                    'en' => 'villa-stone',
                ],
                'description' => [
                    'tr' => 'Doğal taş konseptli, jakuzili villa.',
                    'en' => 'Stone concept villa with jacuzzi.',
                ],
                'highlights' => [
                    'tr' => [
                        ['value' => 'Jakuzili'],
                        ['value' => 'Geniş bahçe'],
                    ],
                ],
                'stay_info' => [
                    'tr' => [
                        ['value' => 'Sigara içilmez'],
                    ],
                ],
                'max_guests' => 4,
                'bedroom_count' => 2,
                'bathroom_count' => 1,
                'villa_category_id' => 1,
                'cancellation_policy_id' => 1,
                'location_id' => 1,
                'address_line' => 'Doğa Yolu Sok. 78',
                'latitude' => 36.8012345,
                'longitude' => 28.2288888,
                'nearby' => [],
                'phone' => '+90 555 777 8899',
                'email' => 'info@villastone.com',
                'promo_video_id' => null,
                'is_active' => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($villas as $data) {
            $slug = $data['slug'];
            $base = config('app.locale', 'tr');
            $data['canonical_slug'] = Str::slug($slug[$base] ?? reset($slug) ?? 'villa');

            Villa::create($data);
        }
    }
}
