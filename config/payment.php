<?php

return [
    /**
     * Active payment gateway
     * Options: 'mock' (testing/development), 'mindtrans' (production)
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
     * MindTrans Payment Service
     * Official gateway for production
     * Sign up: https://mindtrans.co.id
     */
    'mindtrans' => [
        'enabled' => !empty(env('MINDTRANS_API_KEY')),
        'api_key' => env('MINDTRANS_API_KEY'),
        'api_secret' => env('MINDTRANS_API_SECRET'),
        'merchant_id' => env('MINDTRANS_MERCHANT_ID'),
        'mode' => env('MINDTRANS_MODE', 'sandbox'),
        'base_url' => env('MINDTRANS_MODE', 'sandbox') === 'production'
            ? 'https://api.mindtrans.co.id/v1'
            : 'https://sandbox.mindtrans.co.id/v1',
        'callback_url' => env('APP_URL') . '/payment/callback/mindtrans',
        'supported_payment_methods' => [
            'bank_transfer',
            'qris',
            'gopay',
            'ovo',
            'dana',
            'linking',
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
