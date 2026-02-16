<?php

return [
    'api_key' => env('DOKU_API_KEY'),
    'secret_key' => env('DOKU_SECRET_KEY'),
    'merchant_id' => env('DOKU_MERCHANT_ID'),
    'mode' => env('DOKU_MODE', 'sandbox'),
    'base_url' => env('DOKU_MODE', 'sandbox') === 'production'
        ? 'https://api.fintech.doku.com'
        : 'https://sandbox.fintech.doku.com',
    
    // Callback settings
    'callback_url' => env('APP_URL') . '/payment/callback/doku',
    'return_url' => env('APP_URL') . '/payment/success',
    'error_url' => env('APP_URL') . '/payment/failed',
    
    // Payment methods
    'supported_payment_methods' => [
        'VIRTUAL_ACCOUNT',
        'QRIS',
        'E_WALLET',
        'BANK_TRANSFER'
    ],
];
