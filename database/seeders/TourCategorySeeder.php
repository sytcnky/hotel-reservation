<?php

namespace Database\Seeders;

use App\Models\TourCategory;
use Illuminate\Database\Seeder;

class TourCategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['tr' => 'Şehir Turu',           'en' => 'City Tour'],
            ['tr' => 'Tekne Turu',           'en' => 'Boat Tour'],
            ['tr' => 'Yat Turu',             'en' => 'Yacht Tour'],
            ['tr' => 'Dalış / Sualtı',       'en' => 'Scuba / Underwater'],
            ['tr' => 'Rafting',              'en' => 'Rafting'],
            ['tr' => 'Jeep Safari',          'en' => 'Jeep Safari'],
            ['tr' => 'Doğa & Yürüyüş',       'en' => 'Nature & Hiking'],
            ['tr' => 'Kültür & Tarih',       'en' => 'Culture & History'],
            ['tr' => 'Sanat & Müzeler',      'en' => 'Art & Museums'],
            ['tr' => 'Gastronomi',           'en' => 'Food & Wine'],
            ['tr' => 'Aile Dostu',           'en' => 'Family Friendly'],
            ['tr' => 'Macera',               'en' => 'Adventure'],
            ['tr' => 'Gece Hayatı',          'en' => 'Nightlife'],
            ['tr' => 'Alışveriş',            'en' => 'Shopping'],
            ['tr' => 'Spa & Wellness',       'en' => 'Spa & Wellness'],
            ['tr' => 'Foto Safari',          'en' => 'Photo Safari'],
            ['tr' => 'Özel Tur',             'en' => 'Private Tour'],
            ['tr' => 'Küçük Grup',           'en' => 'Small Group'],
            ['tr' => 'VİP / Lüks',           'en' => 'VIP / Luxury'],
            ['tr' => 'Kıyı Gezisi (Cruise)', 'en' => 'Shore Excursion'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => \Str::slug($name['tr']),
                'en' => \Str::slug($name['en']),
            ];

            $existing = TourCategory::query()
                ->whereRaw("slug->> 'tr' = ?", [$slug['tr']])
                ->orWhereRaw("slug->> 'en' = ?", [$slug['en']])
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
                TourCategory::create([
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
