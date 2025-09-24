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
            'query' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/payment/status',
            'refund' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/payment/refund',
            'refund/status' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/payment/refund',
            'search' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/general/searchTransaction',
            // Agreement APIs for tokenized checkout
            'agreement/create' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/create',
            'agreement/execute' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/execute',
            'agreement/query' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/agreement/status',
            'agreement/cancel' => 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/agreement/cancel',
        ],
        'production' => [
            'token' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/token/grant',
            'token/refresh' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/token/refresh',
            'create' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/create',
            'execute' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/execute',
            'query' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/payment/status',
            'refund' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/payment/refund',
            'refund/status' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/payment/refund',
            'search' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/general/searchTransaction',
            // Agreement APIs for tokenized checkout
            'agreement/create' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/create',
            'agreement/execute' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/execute',
            'agreement/query' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/agreement/status',
            'agreement/cancel' => 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout/agreement/cancel',
        ],
    ],

    'callback_url' => env('BKASH_CALLBACK_URL', '/bkash/callback'),
    'redirect_url' => env('BKASH_REDIRECT_URL', '/payment/redirect'),

    // Additional configuration options
    'timeout' => env('BKASH_TIMEOUT', 30), // API request timeout in seconds
    'retry_attempts' => env('BKASH_RETRY_ATTEMPTS', 3), // Maximum retry attempts
    'retry_delay' => env('BKASH_RETRY_DELAY', 1000), // Delay between retries in milliseconds
    'token_cache_ttl' => env('BKASH_TOKEN_CACHE_TTL', 3300), // Token cache TTL in seconds (55 minutes)
    'currency' => env('BKASH_CURRENCY', 'BDT'), // Default currency

    // Validation settings
    'validation' => [
        'strict_mode' => env('BKASH_STRICT_VALIDATION', true), // Enable strict parameter validation
        'max_amount' => env('BKASH_MAX_AMOUNT', 999999.99), // Maximum transaction amount
        'min_amount' => env('BKASH_MIN_AMOUNT', 1.00), // Minimum transaction amount
    ],

];
