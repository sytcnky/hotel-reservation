<?php

namespace Database\Seeders;

use App\Models\BoardType;
use Illuminate\Database\Seeder;

class BoardTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => ['tr' => 'Sadece Oda',           'en' => 'Room Only'],           'slug' => ['tr' => 'sadece-oda',          'en' => 'room-only']],
            ['name' => ['tr' => 'Oda + Kahvaltı',       'en' => 'Bed & Breakfast'],     'slug' => ['tr' => 'oda-kahvalti',        'en' => 'bed-breakfast']],
            ['name' => ['tr' => 'Yarım Pansiyon',       'en' => 'Half Board'],          'slug' => ['tr' => 'yarim-pansiyon',      'en' => 'half-board']],
            ['name' => ['tr' => 'Tam Pansiyon',         'en' => 'Full Board'],          'slug' => ['tr' => 'tam-pansiyon',        'en' => 'full-board']],
            ['name' => ['tr' => 'Her Şey Dahil',        'en' => 'All Inclusive'],       'slug' => ['tr' => 'her-sey-dahil',       'en' => 'all-inclusive']],
            ['name' => ['tr' => 'Ultra Her Şey Dahil',  'en' => 'Ultra All Inclusive'], 'slug' => ['tr' => 'ultra-her-sey-dahil', 'en' => 'ultra-all-inclusive']],
        ];

        foreach ($items as $index => $item) {
            $existing = BoardType::query()
                ->where('slug->>tr', $item['slug']['tr'])
                ->orWhere('slug->>en', $item['slug']['en'])
                ->first();

            if ($existing) {
                $existing->fill([
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'description' => $existing->description ?? [],
                    'is_active' => true,
                    'sort_order' => $index,
                ])->save();
            } else {
                BoardType::create([
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'description' => [],
                    'is_active' => true,
                    'sort_order' => $index,
                ]);
            }
        }
    }
}
