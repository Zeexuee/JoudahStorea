<?php

return [
    'api_key' => env('RAJAONGKIR_API_KEY'),
    'base_url' => 'https://api.rajaongkir.com/api',
    'account_type' => env('RAJAONGKIR_ACCOUNT_TYPE', 'starter'),
    
    // Origin city for shipping calculation
    'origin_city_id' => env('RAJAONGKIR_ORIGIN_CITY_ID', 1575), // Jakarta Barat
    'origin_name' => 'Jakarta Barat',
    'origin_address' => 'Jl. Joglo Raya No. 67, RT. 004/RW. 003, Joglo, Kembangan, Jakarta Barat 11640',
    
    // Supported couriers
    'couriers' => [
        'jne' => [
            'name' => 'JNE',
            'services' => ['REG', 'OKE']
        ],
        'pos' => [
            'name' => 'POS Indonesia',
            'services' => ['REG']
        ],
        'tiki' => [
            'name' => 'TIKI',
            'services' => ['REG', 'ECO']
        ]
    ],
    
    // Cache shipping cost for this duration (in minutes)
    'cache_duration' => 60,
];
