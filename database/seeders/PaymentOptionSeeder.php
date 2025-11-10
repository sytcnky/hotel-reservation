<?php

namespace Database\Seeders;

use App\Models\PaymentOption;
use Illuminate\Database\Seeder;

class PaymentOptionSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['tr' => 'Kredi Kartı',      'en' => 'Credit Card'],
            ['tr' => 'Nakit',            'en' => 'Cash'],
            ['tr' => 'Havale / EFT',     'en' => 'Bank Transfer'],
            ['tr' => 'Otelde Ödeme',     'en' => 'Pay at Hotel'],
            ['tr' => 'Online Ödeme',     'en' => 'Online Payment'],
            ['tr' => 'Taksitli Ödeme',   'en' => 'Installments'],
        ];

        foreach ($items as $i => $name) {
            $slug = [
                'tr' => \Str::slug($name['tr']),
                'en' => \Str::slug($name['en']),
            ];

            $existing = PaymentOption::query()
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
                PaymentOption::create([
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
