<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoomsSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = DB::table('hotels')->orderBy('id')->get();
        $views  = DB::table('view_types')->where('is_active', true)->orderBy('id')->get();
        $now = now();

        $rooms = [];

        foreach ($hotels as $h) {
            $rooms[] = [
                'hotel_id' => $h->id,
                'name' => json_encode([
                    'tr' => 'Standart Oda',
                    'en' => 'Standard Room',
                ]),
                'capacity_adults' => 2,
                'capacity_children' => 0,
                'view_type_id' => $views[0]->id ?? null,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $rooms[] = [
                'hotel_id' => $h->id,
                'name' => json_encode([
                    'tr' => 'Aile Odası',
                    'en' => 'Family Room',
                ]),
                'capacity_adults' => 3,
                'capacity_children' => 1,
                'view_type_id' => $views[1]->id ?? $views[0]->id ?? null,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('rooms')->insert($rooms);
    }
}
