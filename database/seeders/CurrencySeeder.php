<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['code' => 'TRY', 'symbol' => '₺', 'exponent' => 2, 'tr' => 'Türk Lirası',     'en' => 'Turkish Lira'],
            ['code' => 'EUR', 'symbol' => '€',  'exponent' => 2, 'tr' => 'Euro',            'en' => 'Euro'],
            ['code' => 'USD', 'symbol' => '$',  'exponent' => 2, 'tr' => 'ABD Doları',      'en' => 'US Dollar'],
            ['code' => 'GBP', 'symbol' => '£',  'exponent' => 2, 'tr' => 'İngiliz Sterlini', 'en' => 'British Pound'],
            // İstersen JPY (¥, exponent=0), KWD (د.ك, exponent=3) de eklenebilir.
        ];

        foreach ($items as $i => $it) {
            $name = ['tr' => $it['tr'], 'en' => $it['en']];
            $slug = ['tr' => \Str::slug($it['tr']), 'en' => \Str::slug($it['en'])];

            $existing = Currency::query()
                ->where('code', $it['code'])
                ->first();

            if ($existing) {
                $existing->fill([
                    'name' => $name,
                    'slug' => $slug,
                    'code' => $it['code'],
                    'symbol' => $it['symbol'],
                    'exponent' => $it['exponent'],
                    'is_active' => true,
                    'sort_order' => $i,
                ])->save();
            } else {
                Currency::create([
                    'name' => $name,
                    'slug' => $slug,
                    'code' => $it['code'],
                    'symbol' => $it['symbol'],
                    'exponent' => $it['exponent'],
                    'is_active' => true,
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
