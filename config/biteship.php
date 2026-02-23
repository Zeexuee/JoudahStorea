<?php

return [
    /**
     * Biteship API Key
     * Dapatkan dari: https://biteship.com/dashboard → Settings → API
     */
    'api_key' => env('BITESHIP_API_KEY'),

    /**
     * Default origin (warehouse) configuration
     */
    'origin_postal_code' => env('BITESHIP_ORIGIN_POSTAL_CODE', '12345'),
    'origin_city' => env('BITESHIP_ORIGIN_CITY', 'Jakarta'),
    'origin_address' => env('BITESHIP_ORIGIN_ADDRESS', 'Gudang Pusat'),

    /**
     * Default couriers untuk checkout
     * Opsi: jne, pos, tiki, grab, gojek, etc (sesuai availability di region)
     */
    'couriers' => ['jne', 'pos', 'tiki'],

    /**
     * Enable/disable Biteship sebagai primary shipping provider
     */
    'enabled' => env('BITESHIP_ENABLED', true),
];
