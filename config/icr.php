<?php

return [

    'payments' => [
        // Aktif ödeme sürücüsü: demo | isbank | vs.
        'driver'    => env('PAYMENT_DRIVER', 'demo'),

        // Pending order için dakika cinsinden TTL
        'order_ttl' => (int) env('PAYMENT_ORDER_TTL', 1),

        // Demo driver için default outcome (success | fail)
        'demo_mode' => env('PAYMENT_DEMO_MODE', 'success'),
    ],

];
