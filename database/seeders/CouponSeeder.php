<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;
use App\Models\Hotel;
use App\Models\Villa;
use App\Models\Tour;
use App\Models\TransferRoute;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $baseCurrency = 'TRY';

        /*
        |--------------------------------------------------------------------------
        | 1) İlk Rezervasyona %5 – Global Order Total
        |--------------------------------------------------------------------------
        */
        Coupon::create([
            'is_active' => true,
            'code'      => 'WELCOME5',

            'title' => [
                'tr' => 'İlk rezervasyonunuza %5 indirim',
                'en' => '5% off on your first booking',
            ],
            'description' => [
                'tr' => 'Yeni kullanıcılar için hoş geldin indirimi.',
                'en' => 'Welcome discount for new users.',
            ],

            'badge_main'  => ['tr' => '%5', 'en' => '5%'],
            'badge_label' => ['tr' => 'İNDİRİM', 'en' => 'OFF'],

            'valid_from'  => now()->subDay(),
            'valid_until' => null, // süresiz

            'discount_type' => 'percent',
            'percent_value' => 5,

            'scope_type' => 'order_total',

            'currency_data' => [
                'TRY' => [
                    'min_booking_amount' => 0,
                    'max_discount_amount' => 300, // opsiyonel
                ],
            ],

            'is_exclusive'      => false,
            'max_uses_per_user' => 1,
        ]);


        /*
        |--------------------------------------------------------------------------
        | 2) 6+ Gece Rezervasyonlarında Ekstra %10 (otel/villa)
        |--------------------------------------------------------------------------
        */
        Coupon::create([
            'is_active' => true,
            'code'      => 'STAY6GET10',

            'title' => [
                'tr' => '6+ gece konaklamalarda %10 indirim',
                'en' => '10% off for stays of 6 nights or more',
            ],
            'description' => [
                'tr' => 'Uzun süreli konaklamalara özel ek indirim.',
                'en' => 'Additional discount for long stays.',
            ],

            'badge_main'  => ['tr' => '%10', 'en' => '10%'],
            'badge_label' => ['tr' => 'İNDİRİM', 'en' => 'OFF'],

            'valid_from'  => now()->subDay(),
            'valid_until' => null,

            'discount_type' => 'percent',
            'percent_value' => 10,

            'scope_type'   => 'product_type',
            'product_types'=> ['hotel','villa'],

            'min_nights' => 6,

            'currency_data' => [
                'TRY' => [
                    'min_booking_amount' => 0,
                    'max_discount_amount' => 500,
                ],
            ],

            'is_exclusive'      => false,
            'max_uses_per_user' => 5,
        ]);


        /*
        |--------------------------------------------------------------------------
        | 3) Transferlerde 200₺ İndirim — Tekil ürün
        |--------------------------------------------------------------------------
        */
        $transferRoute = TransferRoute::first(); // herhangi bir route

        if ($transferRoute) {
            Coupon::create([
                'is_active' => true,
                'code'      => 'TR200',

                'title' => [
                    'tr' => 'Transfer için 200₺ indirim',
                    'en' => '200 TRY off on transfer',
                ],
                'description' => [
                    'tr' => 'Belirli bir transfer rotasında geçerlidir.',
                    'en' => 'Valid for a specific transfer route.',
                ],

                'badge_main'  => ['tr' => '200₺', 'en' => '₺200'],
                'badge_label' => ['tr' => 'İNDİRİM', 'en' => 'OFF'],

                'valid_from'  => now()->subDay(),
                'valid_until' => now()->addMonth(),

                'discount_type' => 'amount',

                'scope_type'     => 'product',
                'product_domain' => 'transfer',
                'product_id'     => $transferRoute->id,

                'currency_data' => [
                    'TRY' => [
                        'amount'             => 200,
                        'min_booking_amount' => 0,
                        'max_discount_amount'=> null,
                    ],
                ],

                'is_exclusive'      => true, // Tekli kullanım
                'max_uses_per_user' => 3,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | 4) Turlarda %15 İndirim — Minimum harcama 1000 TRY
        |--------------------------------------------------------------------------
        */
        Coupon::create([
            'is_active' => true,
            'code'      => 'TOUR15',

            'title' => [
                'tr' => 'Turlarda %15 indirim',
                'en' => '15% off on tours',
            ],
            'description' => [
                'tr' => '1.000₺ üzeri tüm turlarda geçerli.',
                'en' => 'Valid for all tours above 1000 TRY.',
            ],

            'badge_main'  => ['tr' => '%15', 'en' => '15%'],
            'badge_label' => ['tr' => 'İNDİRİM', 'en' => 'OFF'],

            'valid_from'  => now()->subDay(),
            'valid_until' => now()->addMonths(2),

            'discount_type' => 'percent',
            'percent_value' => 15,

            'scope_type' => 'product_type',
            'product_types'=> ['tour'],

            'currency_data' => [
                'TRY' => [
                    'min_booking_amount' => 1000,
                    'max_discount_amount'=> 600,
                ],
            ],

            'is_exclusive'      => false,
            'max_uses_per_user' => 5,
        ]);


        /*
        |--------------------------------------------------------------------------
        | 5) Özel Kişiye Tanımlanan 300₺ Kupon
        |--------------------------------------------------------------------------
        */
        Coupon::create([
            'is_active' => true,
            'code'      => 'VIP300',

            'title' => [
                'tr' => 'Size özel 300₺ indirim',
                'en' => 'Exclusive 300 TRY discount',
            ],
            'description' => [
                'tr' => 'Özel müşterilere tanımlanan kupon.',
                'en' => 'Coupon assigned for special customers.',
            ],

            'badge_main'  => ['tr' => '300₺', 'en' => '₺300'],
            'badge_label' => ['tr' => 'ÖZEL', 'en' => 'EXTRA'],

            'valid_from'  => now()->subDay(),
            'valid_until' => now()->addDays(10),

            'discount_type' => 'amount',

            'scope_type'   => 'order_total',

            'currency_data' => [
                'TRY' => [
                    'amount'             => 300,
                    'min_booking_amount' => 0,
                    'max_discount_amount'=> null,
                ],
            ],

            'is_exclusive'      => true,
            'max_uses_per_user' => 1,
        ]);
    }
}
