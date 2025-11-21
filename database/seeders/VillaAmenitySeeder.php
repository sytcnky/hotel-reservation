<?php

namespace Database\Seeders;

use App\Models\VillaAmenity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VillaAmenitySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Mutfak / ekipman
            ['tr' => 'Açık Mutfak', 'en' => 'Open Kitchen'],
            ['tr' => 'Çay Makinesi', 'en' => 'Tea Maker'],
            ['tr' => 'Kahve Makinesi', 'en' => 'Coffee Machine'],
            ['tr' => 'Fırın', 'en' => 'Oven'],
            ['tr' => 'Mikrodalga', 'en' => 'Microwave'],
            ['tr' => 'Tost Makinesi', 'en' => 'Toaster'],
            ['tr' => 'Su Isıtıcı', 'en' => 'Kettle'],
            ['tr' => 'Bulaşık Makinesi', 'en' => 'Dishwasher'],
            ['tr' => 'Buzdolabı / Derin Dondurucu', 'en' => 'Fridge / Freezer'],
            ['tr' => 'Çamaşır Makinesi', 'en' => 'Washing Machine'],
            ['tr' => 'Kurutma Makinesi', 'en' => 'Tumble Dryer'],

            // Havuz / dış alan
            ['tr' => 'Yetişkin Havuzu', 'en' => 'Adult Pool'],
            ['tr' => 'Çocuk Havuzu', 'en' => 'Children’s Pool'],
            ['tr' => 'Isıtmalı Havuz', 'en' => 'Heated Pool'],
            ['tr' => 'Korunaklı Havuz', 'en' => 'Secluded Pool'],
            ['tr' => 'Jakuzi', 'en' => 'Jacuzzi'],
            ['tr' => 'Sauna', 'en' => 'Sauna'],
            ['tr' => 'Barbekü', 'en' => 'BBQ'],
            ['tr' => 'Barbekü Alanı (Mangal)', 'en' => 'BBQ Area (Grill)'],
            ['tr' => 'Hamak', 'en' => 'Hammock'],
            ['tr' => 'Bahçe Mobilyası', 'en' => 'Garden Furniture'],
            ['tr' => 'Güneşlenme Terası', 'en' => 'Sun Terrace'],
            ['tr' => 'Şezlong & Şemsiye', 'en' => 'Sunbeds & Umbrella'],
            ['tr' => 'Mini Oyun Parkı', 'en' => 'Mini Playground'],
            ['tr' => 'Özel Otopark', 'en' => 'Private Parking'],
            ['tr' => 'Gazebo / Kamelya', 'en' => 'Gazebo / Pergola'],

            // İklim/konfor
            ['tr' => 'Klima', 'en' => 'Air Conditioning'],
            ['tr' => 'Şömine', 'en' => 'Fireplace'],

            // İçeride eğlence/teknoloji
            ['tr' => 'Smart TV', 'en' => 'Smart TV'],
            ['tr' => 'Netflix / Streaming', 'en' => 'Netflix / Streaming'],
            ['tr' => 'Wi-Fi', 'en' => 'Wi-Fi'],
            ['tr' => 'İnternet (Fiber)', 'en' => 'Fiber Internet'],
            ['tr' => 'Sinema Odası', 'en' => 'Cinema Room'],
            ['tr' => 'Oyun Konsolu', 'en' => 'Game Console'],
            ['tr' => 'Bilardo Masası', 'en' => 'Billiard Table'],
            ['tr' => 'Masa Tenisi', 'en' => 'Table Tennis'],

            // Güvenlik
            ['tr' => 'Alarm Sistemi', 'en' => 'Alarm System'],
            ['tr' => 'Kamera Sistemi', 'en' => 'CCTV'],
            ['tr' => 'Duman Dedektörü', 'en' => 'Smoke Detector'],
            ['tr' => 'Yangın Söndürücü', 'en' => 'Fire Extinguisher'],

            // Bebek/çocuk
            ['tr' => 'Mama Sandalyesi', 'en' => 'High Chair'],
            ['tr' => 'Bebek Park Yatağı', 'en' => 'Baby Cot'],
            ['tr' => 'Bebek Güvenlik Kapısı', 'en' => 'Baby Safety Gate'],

            // Manzara / diğer
            ['tr' => 'Deniz Manzarası', 'en' => 'Sea View'],
            ['tr' => 'Doğa Manzarası', 'en' => 'Nature View'],
            ['tr' => 'Evcil Hayvan Uygun', 'en' => 'Pet Friendly'],
            ['tr' => 'Elektrikli Araç Şarjı', 'en' => 'EV Charger'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => Str::slug($name['tr']),
                'en' => Str::slug($name['en']),
            ];

            $existing = VillaAmenity::query()
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
                VillaAmenity::create([
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
