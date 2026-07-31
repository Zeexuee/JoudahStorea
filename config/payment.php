<?php

return [
    /**
     * Active payment gateway
     * Options: 'mock' (testing/development), 'mindtrans' (Midtrans production)
     */
    'gateway' => env('PAYMENT_GATEWAY', 'mock'),

    /**
     * Mock Payment Service (for testing/development)
     */
    'mock' => [
        'enabled' => true,
        'debug_mode' => env('APP_DEBUG', true),
        'description' => 'Mock payment gateway for testing without real charges',
    ],

    /**
     * Midtrans Payment Service (Snap API)
     * Official Indonesian payment gateway
     * Sign up: https://midtrans.com
     * Dashboard Sandbox: https://dashboard.sandbox.midtrans.com
     * Dashboard Production: https://dashboard.midtrans.com
     * Documentation: https://docs.midtrans.com
     */
    'mindtrans' => [
        'enabled' => !empty(env('MINDTRANS_API_KEY')),
        'api_key' => env('MINDTRANS_API_KEY'), // Server Key from Dashboard
        'api_secret' => env('MINDTRANS_API_SECRET'), // Client Key from Dashboard (optional for backend)
        'mode' => env('MINDTRANS_MODE', 'sandbox'), // 'sandbox' or 'production'
        // Testing override: Midtrans uses integer IDR, so minimum practical amount is 1 IDR.
        'force_test_amount' => env('MINDTRANS_FORCE_TEST_AMOUNT', false),
        'testing_amount_idr' => env('MINDTRANS_TESTING_AMOUNT_IDR', 1),
        'callback_url' => env('APP_URL') . '/payment/callback/mindtrans',
        'supported_payment_methods' => [
            'credit_card',
            'bank_transfer',
            'cimb_clicks',
            'bca_klikbca',
            'bca_klikpay',
            'bri_epay',
            'echannel',
            'mandiri_clickpay',
            'gopay',
            'shopeepay',
            'qris',
            'indomaret',
            'alfamart',
            'akulaku',
        ],
    ],

    /**
     * Payment status mapping and configuration
     */
    'status' => [
        'pending' => [
            'label' => 'Menunggu Pembayaran',
            'color' => 'yellow',
            'icon' => 'clock',
        ],
        'processing' => [
            'label' => 'Diproses',
            'color' => 'blue',
            'icon' => 'spinner',
        ],
        'completed' => [
            'label' => 'Berhasil',
            'color' => 'green',
            'icon' => 'check',
        ],
        'failed' => [
            'label' => 'Gagal',
            'color' => 'red',
            'icon' => 'x',
        ],
        'expired' => [
            'label' => 'Kadaluarsa',
            'color' => 'gray',
            'icon' => 'calendar-x',
        ],
        'cancelled' => [
            'label' => 'Dibatalkan',
            'color' => 'gray',
            'icon' => 'ban',
        ],
        'refunded' => [
            'label' => 'Dikembalikan',
            'color' => 'orange',
            'icon' => 'undo',
        ],
    ],

    /**
     * Payment timeout (in minutes)
     * After this time, pending payments will be marked as expired
     */
    'timeout' => env('PAYMENT_TIMEOUT', 60),

    /**
     * Logging and debugging
     */
    'logging' => [
        'enabled' => env('PAYMENT_LOG_ENABLED', true),
        'channel' => 'single',
    ],
];
