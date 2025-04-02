<?php

use Illuminate\Support\Facades\Http;
use SabitAhmad\Bkash\Exceptions\BkashException;
use SabitAhmad\Bkash\Facades\Bkash;
use SabitAhmad\Bkash\Responses\PaymentResponse;

beforeEach(function () {
    // Set sandbox mode for testing
    config(['bkash.sandbox' => true]);

    // Mock successful token response
    Http::fake([
        'checkout.sandbox.bka.sh/v1.2.0-beta/checkout/token/grant' => Http::response([
            'id_token' => 'fake-token',
            'status' => 'success'
        ]),
    ]);
});

it('creates payment with valid url', function () {
    // Mock successful payment creation
    Http::fake([
        'checkout.sandbox.bka.sh/v1.2.0-beta/checkout/payment/create' => Http::response([
            'paymentID' => 'TRX123',
            'bkashURL' => 'https://checkout.sandbox.bka.sh/payment/TRX123',
            'status' => 'success'
        ]),
    ]);

    $response = Bkash::createPayment(100.00, 'INV-TEST');

    expect($response)
        ->toBeInstanceOf(PaymentResponse::class)
        ->getPaymentUrl()->toBe('https://checkout.sandbox.bka.sh/payment/TRX123')
        ->getPaymentId()->toBe('TRX123')
        ->isSuccess()->toBeTrue();
});

it('handles missing payment url in response', function () {
    // Mock response missing bkashURL
    Http::fake([
        'checkout.sandbox.bka.sh/v1.2.0-beta/checkout/payment/create' => Http::response([
            'paymentID' => 'TRX124',
            'status' => 'success'
        ]),
    ]);

    $response = Bkash::createPayment(100.00, 'INV-TEST');

    expect($response)
        ->getPaymentUrl()->toBeNull()
        ->isSuccess()->toBeTrue();
});

it('handles payment creation failure', function () {
    // Mock failed payment creation
    Http::fake([
        'checkout.sandbox.bka.sh/v1.2.0-beta/checkout/payment/create' => Http::response([
            'errorMessage' => 'Invalid amount',
            'status' => 'fail'
        ], 400),
    ]);

    $response = Bkash::createPayment(0.00, 'INV-TEST');

    expect($response)
        ->isSuccess()->toBeFalse()
        ->getErrorMessage()->toBe('Invalid amount');
});

it('auto-refreshes token on 401 error', function () {
    // Mock token refresh sequence
    Http::fakeSequence()
        ->push(['id_token' => 'expired-token'], 200)
        ->push(['error' => 'Unauthorized'], 401)
        ->push(['id_token' => 'new-token'], 200)
        ->push([
            'paymentID' => 'TRX125',
            'bkashURL' => 'https://checkout.sandbox.bka.sh/payment/TRX125',
            'status' => 'success'
        ], 200);

    $response = Bkash::createPayment(100.00, 'INV-REFRESH-TEST');

    expect($response)
        ->getPaymentUrl()->toBe('https://checkout.sandbox.bka.sh/payment/TRX125')
        ->isSuccess()->toBeTrue();
});

it('handles invalid api responses', function () {
    // Mock invalid JSON response
    Http::fake([
        'checkout.sandbox.bka.sh/v1.2.0-beta/checkout/payment/create' => Http::response(
            'Invalid Server Error',
            500
        ),
    ]);

    $this->expectException(BkashException::class);

    Bkash::createPayment(100.00, 'INV-TEST');
});
