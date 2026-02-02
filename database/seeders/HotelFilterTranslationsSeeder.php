<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Translation;

class HotelFilterTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            // Labels
            [
                'group' => 'hotel_filter',
                'key' => 'label.category',
                'values' => [
                    'tr' => 'Otel Kategorisi',
                    'en' => 'Hotel Category',
                ],
            ],
            [
                'group' => 'hotel_filter',
                'key' => 'label.date_range',
                'values' => [
                    'tr' => 'Tarih aralığı',
                    'en' => 'Date range',
                ],
            ],
            [
                'group' => 'hotel_filter',
                'key' => 'label.city',
                'values' => [
                    'tr' => 'Şehir',
                    'en' => 'City',
                ],
            ],
            [
                'group' => 'hotel_filter',
                'key' => 'label.district',
                'values' => [
                    'tr' => 'İlçe',
                    'en' => 'District',
                ],
            ],
            [
                'group' => 'hotel_filter',
                'key' => 'label.area',
                'values' => [
                    'tr' => 'Bölge',
                    'en' => 'Area',
                ],
            ],
            [
                'group' => 'hotel_filter',
                'key' => 'label.board_type',
                'values' => [
                    'tr' => 'Konaklama Tipi',
                    'en' => 'Board Type',
                ],
            ],
            [
                'group' => 'hotel_filter',
                'key' => 'label.guests',
                'values' => [
                    'tr' => 'Oda Kapasitesi (kişi)',
                    'en' => 'Room Capacity (guests)',
                ],
            ],

            // Options / placeholders
            [
                'group' => 'hotel_filter',
                'key' => 'option.select',
                'values' => [
                    'tr' => 'Seçiniz',
                    'en' => 'Select',
                ],
            ],
            [
                'group' => 'hotel_filter',
                'key' => 'placeholder.date',
                'values' => [
                    'tr' => 'Tarih Seçin',
                    'en' => 'Select date',
                ],
            ],

            // Actions
            [
                'group' => 'hotel_filter',
                'key' => 'action.apply',
                'values' => [
                    'tr' => 'Uygula',
                    'en' => 'Apply',
                ],
            ],
            [
                'group' => 'hotel_filter',
                'key' => 'action.clear',
                'values' => [
                    'tr' => 'Temizle',
                    'en' => 'Clear',
                ],
            ],
        ];

        foreach ($translations as $data) {
            Translation::updateOrCreate(
                [
                    'group' => 'hotel_filter',
                    'key'   => $data['key'],
                ],
                [
                    'values' => $data['values'],
                ]
            );
        }
    }
}
