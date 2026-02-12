<?php

return [
    'api_token' => env('MOOTA_API_TOKEN'),
    'secret_key' => env('MOOTA_SECRET_KEY'),
    'base_url' => env('MOOTA_BASE_URL', 'https://app.moota.co/api/v2/'),
    'bank_accounts' => [
        [
            'bank_type' => 'bca',
            'account_number' => env('MOOTA_BCA_ACCOUNT', ''),
            'name' => env('MOOTA_BCA_NAME', ''),
            'bank_id' => env('MOOTA_BCA_ID', ''), // ID Bank di Dashboard Moota
        ],
        // Tambahkan bank lain jika perlu
    ],
    'webhook_url' => env('MOOTA_WEBHOOK_URL', env('APP_URL').'/api/webhook/moota'),
];
