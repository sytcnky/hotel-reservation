<?php

return [

    'payments' => [

        /*
        |--------------------------------------------------------------------------
        | Aktif Ödeme Driver
        |--------------------------------------------------------------------------
        |
        | none | nestpay
        |
        */
        'driver' => env('PAYMENT_DRIVER', 'none'),

        /*
        |--------------------------------------------------------------------------
        | Pending order TTL (dakika)
        |--------------------------------------------------------------------------
        */
        'order_ttl' => (int) env('PAYMENT_ORDER_TTL', 15),

        /*
        |--------------------------------------------------------------------------
        | Callback Mode
        |--------------------------------------------------------------------------
        |
        | real      → Bankadan gerçek callback beklenir
        | simulate  → Local test için manuel simulate endpoint/command
        |
        */
        'callback_mode' => env('PAYMENT_CALLBACK_MODE', 'real'),

        /*
        |--------------------------------------------------------------------------
        | Nestpay (3D Pay Hosting)
        |--------------------------------------------------------------------------
        */
        'nestpay' => [

            /*
            |--------------------------------------------------------------------------
            | Banka Endpoint
            |--------------------------------------------------------------------------
            */
            'endpoint' => env('NESTPAY_ENDPOINT'),

            /*
            |--------------------------------------------------------------------------
            | Merchant Credentials
            |--------------------------------------------------------------------------
            */
            'client_id' => env('NESTPAY_CLIENT_ID'),
            'store_key' => env('NESTPAY_STORE_KEY'),

            /*
            |--------------------------------------------------------------------------
            | Hash Algoritması
            |--------------------------------------------------------------------------
            */
            'hash_algorithm' => 'ver3',

            /*
            |--------------------------------------------------------------------------
            | Callback Hash Verify Policy
            |--------------------------------------------------------------------------
            |
            | Prod / staging: zorunlu true
            | Local: simulate modda false olabilir
            |
            */
            'verify_callback_hash' => (bool) env('NESTPAY_VERIFY_CALLBACK_HASH', true),
        ],
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
