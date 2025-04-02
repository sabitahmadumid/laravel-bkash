# Laravel bKash Payment Gateway Integration

[![Latest Version on Packagist](https://img.shields.io/packagist/v/sabitahmad/laravel-bkash.svg?style=flat-square)](https://packagist.org/packages/sabitahmad/laravel-bkash)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/sabitahmad/laravel-bkash/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/sabitahmad/laravel-bkash/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/sabitahmad/laravel-bkash/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/sabitahmad/laravel-bkash/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/sabitahmad/laravel-bkash.svg?style=flat-square)](https://packagist.org/packages/sabitahmad/laravel-bkash)

A comprehensive solution for integrating bKash payments into Laravel applications.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Response Handling](#response-handling)
- [Security](#security)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

---

## Features
- Full bKash API v1.2.0 integration
- Sandbox & Production modes
- Token management with auto-refresh
- Payment, Refund, and Query operations
- Comprehensive exception handling
- Transaction logging with UI component
- Event-driven architecture
- Customizable responses
- Built-in Laravel HTTP Client

## Requirements
- PHP 8.0+
- Laravel 9.x+
- Composer
- bKash Merchant Account

---

## Installation

You can install the package via composer:

```bash
composer require sabitahmad/laravel-bkash
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-bkash-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-bkash-config"
```

This is the contents of the published config file:

```php
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
            'token' => 'https://checkout.sandbox.bka.sh/v1.2.0-beta/checkout/token/grant',
            // ... other endpoints
        ],
        'production' => [
            // Production endpoints
        ]
    ],
    'callback_url' => env('BKASH_CALLBACK_URL', '/bkash/callback'),
    'redirect_url' => env('BKASH_REDIRECT_URL', '/payment/redirect'),
];
```

Optionally, you can publish the views using

[//]: # (```bash)

[//]: # (php artisan vendor:publish --tag="laravel-bkash-views")

[//]: # (```)

## Environment Variables

Add the following environment variables to your `.env` file:

```dotenv
BKASH_SANDBOX=true
BKASH_APP_KEY=your_app_key
BKASH_APP_SECRET=your_app_secret
BKASH_USERNAME=your_username
BKASH_PASSWORD=your_password
BKASH_CALLBACK_URL=/bkash/callback
BKASH_REDIRECT_URL=/payment/redirect
BKASH_LOG_TRANSACTIONS=true
```
## Usage

### Create Payment
```php
try {
    $payment = Bkash::createPayment(payerReference: 'CUST-001', amount: 100.50, invoiceNumber: 'INV-001'
);

    
    if ($payment->isSuccess()) {
        return redirect()->away($payment->getPaymentUrl());
    }
    
    throw new Exception('Payment initialization failed: ' . $payment->getErrorMessage());
    
} catch (BkashException $e) {
    // Handle exception
}
```

### Execute Payment
```php

$execution = Bkash::executePayment($paymentId);

if ($execution->getTransactionStatus() === 'Completed') {
    // Payment successful
    $trxId = $execution->getTrxId();
}

```

### Handle Callback

```php
Route::post('/bkash/callback', function (Request $request) {
    $payment = Bkash::executePayment($request->paymentID);
    
    if ($payment->isSuccess()) {
        event(new PaymentCompleted($payment));
        return redirect('/success');
    }
    
    return redirect('/failed');
});
```

# Response Handling

### PaymentResponse Methods

```php
$response->getPaymentId(): ?string      // TRX123456
$response->getPaymentUrl(): ?string     // https://sandbox.payment.bkash.com/?paymentId=TR0011dQPHnuY1720518383420
$response->getAmount(): ?float          // 100.50
$response->getInvoiceNumber(): ?string  // INV-2023-001
$response->isSuccess(): bool
$response->getErrorMessage(): ?string
```
### ExecutePaymentResponse Methods

```php
$response->getTransactionStatus(): ?string // Completed
$response->getTrxId(): ?string     // TRX123456
$response->getPaymentExecuteTime(): ?string   // 2023-10-01T12:00:00+06:00
```




## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Sabit Ahmad](https://github.com/SabitAhmad)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
