<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Translation;

class RouteTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // Ana sayfa
            [
                'key' => 'home',
                'values' => [
                    'tr' => '',
                    'en' => '',
                ],
            ],

            // Transfers
            [
                'key' => 'transfers',
                'values' => [
                    'tr' => 'transferler',
                    'en' => 'transfers',
                ],
            ],

            // Hotels list + detail
            [
                'key' => 'hotels',
                'values' => [
                    'tr' => 'oteller',
                    'en' => 'hotels',
                ],
            ],
            [
                'key' => 'hotel.detail',
                'values' => [
                    'tr' => 'otel/{id}',
                    'en' => 'hotel/{id}',
                ],
            ],

            // Villas
            [
                'key' => 'villa',
                'values' => [
                    'tr' => 'villalar',
                    'en' => 'villas',
                ],
            ],
            [
                'key' => 'villa.villa-detail',
                'values' => [
                    'tr' => 'villa/{slug}',
                    'en' => 'villa/{slug}',
                ],
            ],

            // Excursions
            [
                'key' => 'excursions',
                'values' => [
                    'tr' => 'gunluk-turlar',
                    'en' => 'excursions',
                ],
            ],
            [
                'key' => 'excursions.detail',
                'values' => [
                    'tr' => 'gunluk-turlar/{slug}',
                    'en' => 'excursions/{slug}',
                ],
            ],

            // Statik sayfalar
            [
                'key' => 'contact',
                'values' => [
                    'tr' => 'iletisim',
                    'en' => 'contact',
                ],
            ],
            [
                'key' => 'help',
                'values' => [
                    'tr' => 'yardim',
                    'en' => 'help',
                ],
            ],
            [
                'key' => 'payment',
                'values' => [
                    'tr' => 'odeme',
                    'en' => 'payment',
                ],
            ],
            [
                'key' => 'success',
                'values' => [
                    'tr' => 'odeme/basarili',
                    'en' => 'success',
                ],
            ],
            [
                'key' => 'cart',
                'values' => [
                    'tr' => 'sepet',
                    'en' => 'cart',
                ],
            ],

            // Account alanı
            [
                'key' => 'account.dashboard',
                'values' => [
                    'tr' => 'hesabim',
                    'en' => 'account',
                ],
            ],
            [
                'key' => 'account.bookings',
                'values' => [
                    'tr' => 'hesabim/rezervasyonlarim',
                    'en' => 'account/bookings',
                ],
            ],
            [
                'key' => 'account.coupons',
                'values' => [
                    'tr' => 'hesabim/kuponlarim',
                    'en' => 'account/coupons',
                ],
            ],
            [
                'key' => 'account.tickets',
                'values' => [
                    'tr' => 'hesabim/destek-taleplerim',
                    'en' => 'account/tickets',
                ],
            ],
            [
                'key' => 'account.tickets.show',
                'values' => [
                    'tr' => 'hesabim/destek-taleplerim/{id}',
                    'en' => 'account/tickets/{id}',
                ],
            ],
            [
                'key' => 'account.settings',
                'values' => [
                    'tr' => 'hesabim/ayarlar',
                    'en' => 'account/settings',
                ],
            ],
            [
                'key' => 'account.settings.update',
                'values' => [
                    'tr' => 'hesabim/ayarlar',
                    'en' => 'account/settings',
                ],
            ],
            [
                'key' => 'account.password.update',
                'values' => [
                    'tr' => 'hesabim/sifre',
                    'en' => 'account/password',
                ],
            ],

            // Guides
            [
                'key' => 'guides',
                'values' => [
                    'tr' => 'gezi-rehberi',
                    'en' => 'guides',
                ],
            ],
            [
                'key' => 'guides.show',
                'values' => [
                    'tr' => 'gezi-rehberi/{slug}',
                    'en' => 'guides/{slug}',
                ],
            ],
        ];

        foreach ($rows as $row) {
            Translation::updateOrCreate(
                ['group' => 'routes', 'key' => $row['key']],
                ['values' => $row['values']],
            );
        }
    }
}
