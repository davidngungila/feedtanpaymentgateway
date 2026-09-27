<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Purpose-separated keys (blind indexes / HMAC, NOT stored in the DB)
    |--------------------------------------------------------------------------
    | Each purpose gets its own key so a hash built for customer lookup can
    | never be confused with a hash built for provider references or API
    | keys. Keys live in the environment / secret manager, never in the DB.
    | When a purpose key is missing we derive a stable fallback from APP_KEY
    | so local/dev keeps working; set real SECURITY_* keys in production.
    */
    'keys' => [
        'credentials' => env('SECURITY_CREDENTIALS_KEY'),
        'customer' => env('SECURITY_CUSTOMER_DATA_KEY'),
        'webhook' => env('SECURITY_WEBHOOK_DATA_KEY'),
        'api' => env('SECURITY_API_KEY'),
        'backup' => env('SECURITY_BACKUP_KEY'),
    ],

    'webhooks' => [
        'require_signature' => env('WEBHOOK_REQUIRE_SIGNATURE', true),
        'tolerance_seconds' => (int) env('WEBHOOK_TIMESTAMP_TOLERANCE', 300),
    ],

    'mask' => [
        'phone_keep_start' => 3,
        'phone_keep_end' => 3,
        'secret_visible' => 4,
    ],
];
