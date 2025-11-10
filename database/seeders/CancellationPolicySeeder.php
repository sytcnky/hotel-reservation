<?php

namespace Database\Seeders;

use App\Models\CancellationPolicy;
use Illuminate\Database\Seeder;

class CancellationPolicySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['tr' => 'Ücretsiz İptal',                 'en' => 'Free Cancellation'],
            ['tr' => 'İade Edilemez (Non-Refundable)', 'en' => 'Non-Refundable'],
            ['tr' => '7 Gün Önceye Kadar Ücretsiz',    'en' => 'Free up to 7 Days'],
            ['tr' => '24 Saat Önceye Kadar Ücretsiz',  'en' => 'Free up to 24 Hours'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => \Str::slug($name['tr']),
                'en' => \Str::slug($name['en']),
            ];

            $existing = CancellationPolicy::query()
                ->whereRaw("slug->> 'tr' = ?", [$slug['tr']])
                ->orWhereRaw("slug->> 'en' = ?", [$slug['en']])
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
                CancellationPolicy::create([
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
