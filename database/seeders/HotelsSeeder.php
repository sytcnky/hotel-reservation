<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HotelsSeeder extends Seeder
{
    public function run(): void
    {
        $areas = DB::table('locations')
            ->where('type', 'area')
            ->where('is_active', true)
            ->orderBy('id')
            ->limit(3)
            ->get();

        $categories = DB::table('hotel_categories')->where('is_active', true)->orderBy('id')->get();
        $stars      = DB::table('star_ratings')->where('is_active', true)->orderBy('rating_value')->get();
        $boards     = DB::table('board_types')->where('is_active', true)->orderBy('id')->get();
        $beaches    = DB::table('beach_types')->where('is_active', true)->orderBy('id')->get();

        $now = now();
        $hotels = [];

        for ($i = 1; $i <= 12; $i++) {
            $isApart = $i > 9; // son 3 apart
            $area = $areas[($i - 1) % $areas->count()];

            $category = $isApart
                ? $categories->firstWhere('slug->tr', 'apart')
                : $categories[($i - 1) % $categories->count()];

            $star = $isApart ? null : $stars[($i - 1) % $stars->count()];

            $hotels[] = [
                'code' => 'HTL-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => json_encode([
                    'tr' => "Test Otel $i",
                    'en' => "Test Hotel $i",
                ]),
                'slug' => json_encode([
                    'tr' => "test-otel-$i",
                    'en' => "test-hotel-$i",
                ]),
                'canonical_slug' => "test-hotel-$i",
                'location_id' => $area->id,
                'hotel_category_id' => $category?->id,
                'star_rating_id' => $star?->id,
                'board_type_id' => $boards[($i - 1) % $boards->count()]->id,
                'beach_type_id' => $beaches[($i - 1) % $beaches->count()]->id,
                'is_active' => $i <= 10, // 2 pasif
                'sort_order' => $i,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('hotels')->insert($hotels);
    }
}
