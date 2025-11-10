<?php

namespace Database\Seeders;

use App\Models\ViewType;
use Illuminate\Database\Seeder;

class ViewTypeSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['tr' => 'Deniz Manzaralı', 'en' => 'Sea View'],
            ['tr' => 'Havuz Manzaralı', 'en' => 'Pool View'],
            ['tr' => 'Dağ Manzaralı', 'en' => 'Mountain View'],
            ['tr' => 'Bahçe Manzaralı', 'en' => 'Garden View'],
            ['tr' => 'Kısmi Deniz Manzaralı', 'en' => 'Partial Sea View'],
            ['tr' => 'Şehir Manzaralı', 'en' => 'City View'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => \Str::slug($name['tr']),
                'en' => \Str::slug($name['en']),
            ];

            $existing = ViewType::query()
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
                ViewType::create([
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
