<?php

namespace Database\Seeders;

use App\Models\BeachType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BeachTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['tr' => 'Kumsal', 'en' => 'Sandy Beach'],
            ['tr' => 'Platform', 'en' => 'Platform Beach'],
            ['tr' => 'Mavi Bayraklı', 'en' => 'Blue Flag'],
            ['tr' => 'Özel Plaj', 'en' => 'Private Beach'],
            ['tr' => 'Halk Plajı', 'en' => 'Public Beach'],
            ['tr' => 'Denize Sıfır', 'en' => 'Seafront'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => Str::slug($name['tr']),
                'en' => Str::slug($name['en']),
            ];

            $existing = BeachType::query()
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
                BeachType::create([
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
