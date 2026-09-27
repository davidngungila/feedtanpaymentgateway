<?php

/*
|--------------------------------------------------------------------------
| Tanzania mobile-money collection providers
|--------------------------------------------------------------------------
| Five providers, one payment engine. Each provider has its own adapter
| class (see app/Payments/Providers) talking DIRECTLY to that provider's
| own API with that provider's own credentials — no shared aggregator.
| Until a provider's base URL + credentials are configured, its adapter
| fails closed (HTTP 503, clear message) rather than faking success.
|
| Prefix defaults are the common Tanzanian allocations and can be tuned
| from the provider Configuration page.
*/
return [
    'currency' => 'TZS',
    'min_amount' => 500,
    'max_amount' => 5000000,

    'providers' => [
        'mpesa' => [
            'name' => 'M-Pesa',
            'color' => '#E30613',
            'prefixes' => ['74', '75', '76'],
            'driver' => env('MPESA_DRIVER', 'direct'),
            'webhook_secret_env' => 'MPESA_WEBHOOK_SECRET',
            'direct' => [
                'base_url' => env('MPESA_BASE_URL'),
                'initiate_path' => env('MPESA_INITIATE_PATH'),
                'status_path' => env('MPESA_STATUS_PATH'),
                'test_path' => env('MPESA_TEST_PATH', '/'),
                'auth_type' => env('MPESA_AUTH_TYPE', 'api-key'),
            ],
        ],
        'airtel' => [
            'name' => 'Airtel Money',
            'color' => '#ED1C24',
            'prefixes' => ['68', '69', '78'],
            'driver' => env('AIRTEL_DRIVER', 'direct'),
            'webhook_secret_env' => 'AIRTEL_WEBHOOK_SECRET',
            'direct' => [
                'base_url' => env('AIRTEL_BASE_URL'),
                'initiate_path' => env('AIRTEL_INITIATE_PATH'),
                'status_path' => env('AIRTEL_STATUS_PATH'),
                'test_path' => env('AIRTEL_TEST_PATH', '/'),
                'auth_type' => env('AIRTEL_AUTH_TYPE', 'api-key'),
            ],
        ],
        'mixx' => [
            'name' => 'Mixx by Yas',
            'color' => '#00377B',
            'prefixes' => ['65', '67', '71'],
            'driver' => env('MIXX_DRIVER', 'direct'),
            'webhook_secret_env' => 'MIXX_WEBHOOK_SECRET',
            'direct' => [
                'base_url' => env('MIXX_BASE_URL'),
                'initiate_path' => env('MIXX_INITIATE_PATH'),
                'status_path' => env('MIXX_STATUS_PATH'),
                'test_path' => env('MIXX_TEST_PATH', '/'),
                'auth_type' => env('MIXX_AUTH_TYPE', 'api-key'),
            ],
        ],
        'halopesa' => [
            'name' => 'HaloPesa',
            'color' => '#F39200',
            'prefixes' => ['62'],
            'driver' => env('HALOPESA_DRIVER', 'direct'),
            'webhook_secret_env' => 'HALOPESA_WEBHOOK_SECRET',
            'direct' => [
                'base_url' => env('HALOPESA_BASE_URL'),
                'initiate_path' => env('HALOPESA_INITIATE_PATH'),
                'status_path' => env('HALOPESA_STATUS_PATH'),
                'test_path' => env('HALOPESA_TEST_PATH', '/'),
                'auth_type' => env('HALOPESA_AUTH_TYPE', 'api-key'),
            ],
        ],
        'tpesa' => [
            'name' => 'T-Pesa',
            'color' => '#007A33',
            'prefixes' => ['73'],
            'driver' => env('TPESA_DRIVER', 'direct'),
            'webhook_secret_env' => 'TPESA_WEBHOOK_SECRET',
            'direct' => [
                'base_url' => env('TPESA_BASE_URL'),
                'initiate_path' => env('TPESA_INITIATE_PATH'),
                'status_path' => env('TPESA_STATUS_PATH'),
                'test_path' => env('TPESA_TEST_PATH', '/'),
                'auth_type' => env('TPESA_AUTH_TYPE', 'api-key'),
            ],
        ],
    ],
];
