<?php

return [

    'sandbox' => env('BKASH_SANDBOX', true),
    'log_transactions' => env('BKASH_LOG_TRANSACTIONS', true),

    'credentials' => [
        'app_key' => env('BKASH_APP_KEY'),
        'app_secret' => env('BKASH_APP_SECRET'),
        'username' => env('BKASH_USERNAME'),
        'password' => env('BKASH_PASSWORD'),
    ],

    'urls' => [
        'sandbox' => [
            'token' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/token/grant',
            'token/refresh' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/token/refresh',
            'create' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/create',
            'execute' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/execute',
            'refund' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/payment/refund',
            'query' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/payment/status',
        ],
        'production' => [
            'token' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/token/grant',
            'token/refresh' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/token/refresh',
            'create' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/create',
            'execute' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/execute',
            'refund' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/payment/refund',
            'query' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/payment/status',
        ],
    ],

    'callback_url' => env('BKASH_CALLBACK_URL', '/bkash/callback'),
    'redirect_url' => env('BKASH_REDIRECT_URL', '/payment/redirect'),

];
