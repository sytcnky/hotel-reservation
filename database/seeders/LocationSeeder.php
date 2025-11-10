<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Ülke
        $turkey = Location::create([
            'type' => 'country',
            'code' => 'TR',
            'name' => 'Türkiye',
            'slug' => 'turkiye',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // İl
        $mugla = Location::create([
            'parent_id' => $turkey->id,
            'type' => 'province',
            'code' => '48',
            'name' => 'Muğla',
            'slug' => 'mugla',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // İlçeler
        $marmaris = Location::create([
            'parent_id' => $mugla->id,
            'type' => 'district',
            'name' => 'Marmaris',
            'slug' => 'marmaris',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $dalaman = Location::create([
            'parent_id' => $mugla->id,
            'type' => 'district',
            'name' => 'Dalaman',
            'slug' => 'dalaman',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        // Marmaris bölgeleri
        foreach (['İçmeler', 'Armutalan', 'Siteler', 'Bozburun', 'Selimiye'] as $i => $area) {
            Location::create([
                'parent_id' => $marmaris->id,
                'type' => 'area',
                'name' => $area,
                'slug' => Str::slug($area),
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }

        // Dalaman bölgeleri
        foreach (['Merkez', 'Dalaman Havalimanı'] as $i => $area) {
            Location::create([
                'parent_id' => $dalaman->id,
                'type' => 'area',
                'name' => $area,
                'slug' => Str::slug($area),
                'is_active' => true,
                'sort_order' => $i + 1,
            ]);
        }
    }
}
