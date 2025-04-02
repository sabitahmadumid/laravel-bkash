<?php

namespace SabitAhmad\Bkash;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use SabitAhmad\Bkash\Exceptions\BkashException;
use SabitAhmad\Bkash\Models\BkashPayment;
use SabitAhmad\Bkash\Responses\PaymentResponse;
use SabitAhmad\Bkash\Responses\QueryResponse;
use SabitAhmad\Bkash\Responses\RefundResponse;

class Bkash
{
    protected mixed $config;

    protected mixed $shouldLogTransactions;

    public function __construct()
    {
        $this->config = config('bkash');
        $this->shouldLogTransactions = $this->config['log_transactions'] ?? true;
    }

    /**
     * @throws BkashException
     */
    public function createPayment(string $payerReference, float $amount, string $invoiceNumber, ?string $callbackURL = null, ?string $merchantAssociationInfo = null): PaymentResponse
    {

        $callbackURL = $callbackURL ?? config('bkash.callback_url');

        $this->validateParameters(
            $payerReference,
            $invoiceNumber,
            $merchantAssociationInfo,
        );

        $response = $this->makeRequest('POST', $this->getUrl('create'), [
            'mode' => '0011',
            'amount' => (string) $amount,
            'callbackURL' => $callbackURL,
            'payerReference' => $payerReference,
            'currency' => 'BDT',
            'merchantInvoiceNumber' => $invoiceNumber,
            'intent' => 'sale',
        ]);

        if ($this->shouldLogTransactions) {
            $this->logTransaction('create_payment', $response);
        }

        return new PaymentResponse($response);
    }

    /**
     * @throws BkashException
     */
    public function executePayment(string $paymentId): PaymentResponse
    {
        $response = $this->makeRequest('POST', $this->getUrl('execute'), [
            'paymentID' => $paymentId,
        ]);

        if ($this->shouldLogTransactions) {
            $this->logTransaction('execute_payment', $response);
        }

        return new PaymentResponse($response);
    }

    /**
     * @throws BkashException
     */
    public function refundPayment(string $paymentId, float $amount, string $reason): RefundResponse
    {
        $response = $this->makeRequest('POST', $this->getUrl('refund'), [
            'paymentID' => $paymentId,
            'amount' => $amount,
            'reason' => $reason,
        ]);

        if ($this->shouldLogTransactions) {
            $this->logTransaction('refund_payment', $response);
        }

        return new RefundResponse($response);
    }

    /**
     * @throws BkashException
     */
    public function queryPayment(string $paymentId): QueryResponse
    {
        $response = $this->makeRequest('POST', $this->getUrl('query'), [
            'paymentID' => $paymentId,
        ]);

        if ($this->shouldLogTransactions) {
            $this->logTransaction('query_payment', $response);
        }

        return new QueryResponse($response);
    }

    protected function logTransaction(string $type, array $response): void
    {
        BkashPayment::create([
            'type' => $type,
            'payment_id' => $response['paymentID'] ?? null,
            'amount' => $response['amount'] ?? null,
            'status' => $response['status'] ?? 'failed',
            'response' => json_encode($response),
        ]);
    }

    /**
     * @throws BkashException
     */
    protected function makeRequest(string $method, string $url, array $data = []): array
    {
        try {
            $token = $this->getToken();

            $response = Http::withHeaders([
                'Authorization' => $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout(30)
                ->{$method}($url, $data);

            if (! $response->successful()) {
                if ($response->status() === 401) {
                    $this->refreshToken();

                    return $this->makeRequest($method, $url, $data);
                }

                throw new BkashException(
                    'API request failed: '.$response->json('errorMessage', 'Unknown error')
                );
            }

            return $response->json();
        } catch (\Exception $e) {
            throw new BkashException('API request failed: '.$e->getMessage());
        }
    }

    protected function getToken(): string
    {
        return Cache::remember('bkash_token', 3300, function () {
            return $this->generateToken();
        });
    }

    /**
     * @throws BkashException
     * @throws ConnectionException
     */
    protected function generateToken(): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'username' => $this->config['credentials']['username'],
            'password' => $this->config['credentials']['password'],
        ])
            ->post($this->getUrl('token'), [
                'app_key' => $this->config['credentials']['app_key'],
                'app_secret' => $this->config['credentials']['app_secret'],
            ]);

        if (! $response->successful() || ! $response->json('id_token')) {
            throw BkashException::tokenGenerationFailed();
        }

        return $response->json('id_token');
    }

    /**
     * @throws ConnectionException|BkashException
     */
    protected function refreshToken(): string
    {
        $currentToken = $this->getToken();

        $response = Http::withHeaders([
            'Authorization' => $currentToken,
            'Content-Type' => 'application/json',
        ])->post($this->getUrl('token/refresh'), [
            'app_key' => $this->config['credentials']['app_key'],
            'app_secret' => $this->config['credentials']['app_secret'],
        ]);

        if (! $response->successful() || ! $response->json('id_token')) {
            throw BkashException::tokenRefreshFailed();
        }

        Cache::put('bkash_token', $response->json('id_token'), now()->addSeconds(3300));

        return $response->json('id_token');
    }

    protected function getUrl(string $type): string
    {
        $mode = $this->config['sandbox'] ? 'sandbox' : 'production';

        return $this->config['urls'][$mode][$type];
    }

    /**
     * Validate request parameters
     *
     * @throws BkashException
     */
    protected function validateParameters(string $payerReference, string $invoiceNumber, ?string $merchantAssociationInfo): void
    {
        // Validate special characters
        $invalidChars = ['<', '>', '&'];

        if (strlen($payerReference) > 255 ||
            strpbrk($payerReference, implode('', $invalidChars)) !== false) {
            throw new BkashException('Invalid payerReference: contains special characters or exceeds length');
        }

        if (strlen($invoiceNumber) > 255 ||
            strpbrk($invoiceNumber, implode('', $invalidChars)) !== false) {
            throw new BkashException('Invalid invoiceNumber: contains special characters or exceeds length');
        }

        if ($merchantAssociationInfo && (strlen($merchantAssociationInfo) > 255 || strpbrk($merchantAssociationInfo, implode('', $invalidChars)) !== false)) {
            throw new BkashException('Invalid merchantAssociationInfo: contains special characters or exceeds length');
        }
    }
}
