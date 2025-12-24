<?php

namespace Database\Seeders;

use App\Models\SupportTicketCategory;
use Illuminate\Database\Seeder;

class SupportTicketCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'slug_tr' => 'genel',
                'name' => ['tr' => 'Genel', 'en' => 'General'],
                'slug' => ['tr' => 'genel', 'en' => 'general'],
                'requires_order' => false,
                'sort_order' => 10,
            ],
            [
                'slug_tr' => 'uyelik-islemleri',
                'name' => ['tr' => 'Üyelik İşlemleri', 'en' => 'Membership'],
                'slug' => ['tr' => 'uyelik-islemleri', 'en' => 'membership'],
                'requires_order' => false,
                'sort_order' => 20,
            ],
            [
                'slug_tr' => 'siparis',
                'name' => ['tr' => 'Sipariş', 'en' => 'Order'],
                'slug' => ['tr' => 'siparis', 'en' => 'order'],
                'requires_order' => true,
                'sort_order' => 30,
            ],
        ];

        foreach ($items as $item) {
            SupportTicketCategory::query()->updateOrCreate(
                [
                    // JSON alanında TR slug’a göre eşleştiriyoruz
                    'slug->tr' => $item['slug_tr'],
                ],
                [
                    'name' => $item['name'],
                    'slug' => $item['slug'],
                    'requires_order' => $item['requires_order'],
                    'is_active' => true,
                    'sort_order' => $item['sort_order'],
                ]
            );
        }
    }
}
