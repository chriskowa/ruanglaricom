<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi Midtrans payment gateway
    |
    */

    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    /*
    |--------------------------------------------------------------------------
    | Testing Mode
    |--------------------------------------------------------------------------
    |
    | Jika true, pembayaran akan otomatis approved tanpa melalui Midtrans
    | Berguna untuk development dan testing
    |
    */

    'testing_mode' => env('MIDTRANS_TESTING_MODE', false),

    /*
    |--------------------------------------------------------------------------
    | Midtrans API Endpoints
    |--------------------------------------------------------------------------
    |
    | Endpoint untuk sandbox dan production
    |
    */

    'base_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com'
        : 'https://app.sandbox.midtrans.com',
];
