<?php

return [

    'payments' => [
        // Aktif ödeme sürücüsü: demo | isbank | vs.
        'driver'    => env('PAYMENT_DRIVER', 'demo'),

        // Pending order için dakika cinsinden TTL
        'order_ttl' => (int) env('PAYMENT_ORDER_TTL', 15),

        // Demo driver için default outcome (success | fail)
        'demo_mode' => env('PAYMENT_DEMO_MODE', 'success'),
    ],

    'mail' => [
        // Ops ekibi mail hedefleri (virgülle ayrılmış)
        'ops_to' => array_values(array_filter(array_map(
            static fn ($v) => trim($v),
            explode(',', (string) env('ICR_MAIL_OPS_TO', ''))
        ))),

        'from' => [
            'address' => (string) env('ICR_MAIL_FROM_ADDRESS', ''),
            'name'    => (string) env('ICR_MAIL_FROM_NAME', ''),
        ],

        /**
         * Idempotency TTL (days)
         * - Duplicate mail riskini azaltmak için cache key TTL.
         * - Ortamlar arası standart: tüm mail job’ları aynı TTL’yi kullanır.
         */
        'idempotency_days' => (int) env('ICR_MAIL_IDEMPOTENCY_DAYS', 30),
    ],
];
