<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Translation;

class CustomerAccountTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            // Menü
            [
                'group' => 'customer_account',
                'key' => 'menu.dashboard',
                'values' => [
                    'tr' => 'Hesabım',
                    'en' => 'My Account',
                ],
            ],
            [
                'group' => 'customer_account',
                'key' => 'menu.bookings',
                'values' => [
                    'tr' => 'Rezervasyonlarım',
                    'en' => 'My Bookings',
                ],
            ],
            [
                'group' => 'customer_account',
                'key' => 'menu.coupons',
                'values' => [
                    'tr' => 'İndirim Kuponlarım',
                    'en' => 'My Discount Coupons',
                ],
            ],
            [
                'group' => 'customer_account',
                'key' => 'menu.tickets',
                'values' => [
                    'tr' => 'Destek Taleplerim',
                    'en' => 'My Support Tickets',
                ],
            ],
            [
                'group' => 'customer_account',
                'key' => 'menu.settings',
                'values' => [
                    'tr' => 'Üyelik Ayarlarım',
                    'en' => 'Account Settings',
                ],
            ],

            // Dashboard
            [
                'group' => 'customer_account',
                'key' => 'dashboard.greeting',
                'values' => [
                    'tr' => 'Merhaba',
                    'en' => 'Hello',
                ],
            ],
            [
                'group' => 'customer_account',
                'key' => 'dashboard.intro',
                'values' => [
                    'tr' => 'Hesabınızla ilgili ayarları, rezervasyonlarınızı ve destek taleplerinizi buradan yönetebilirsiniz.',
                    'en' => 'You can manage your account settings, bookings and support requests here.',
                ],
            ],
        ];

        foreach ($translations as $data) {
            Translation::updateOrCreate(
                ['group' => $data['group'], 'key' => $data['key']],
                ['values' => $data['values']],
            );
        }
    }
}
