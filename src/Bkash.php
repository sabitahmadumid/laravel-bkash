<?php

namespace SabitAhmad\Bkash;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use SabitAhmad\Bkash\Events\AgreementCreated;
use SabitAhmad\Bkash\Events\PaymentCompleted;
use SabitAhmad\Bkash\Events\PaymentFailed;
use SabitAhmad\Bkash\Exceptions\BkashException;
use SabitAhmad\Bkash\Models\BkashPayment;
use SabitAhmad\Bkash\Responses\AgreementResponse;
use SabitAhmad\Bkash\Responses\PaymentResponse;
use SabitAhmad\Bkash\Responses\QueryResponse;
use SabitAhmad\Bkash\Responses\RefundResponse;
use SabitAhmad\Bkash\Responses\SearchResponse;

class Bkash
{
    protected array $config;

    protected bool $shouldLogTransactions;

    protected string $tokenCacheKey = 'bkash_token';

    protected int $tokenCacheTtl = 3300; // 55 minutes

    public function __construct()
    {
        $this->config = config('bkash', []);
        $this->shouldLogTransactions = $this->config['log_transactions'] ?? true;
    }

    /**
     * Create Agreement for tokenized checkout
     *
     * @throws BkashException
     */
    public function createAgreement(string $payerReference, ?string $callbackURL = null): AgreementResponse
    {
        $callbackURL = $callbackURL ?? $this->config['callback_url'] ?? '';

        $this->validatePayerReference($payerReference);

        $response = $this->makeRequest('POST', $this->getUrl('agreement/create'), [
            'mode' => '0000',
            'callbackURL' => $callbackURL,
            'payerReference' => $payerReference,
            'currency' => 'BDT',
            'intent' => 'sale',
        ]);

        if ($this->shouldLogTransactions) {
            $this->logTransaction('create_agreement', $response);
        }

        return new AgreementResponse($response);
    }

    /**
     * Execute Agreement after user authorization
     *
     * @throws BkashException
     */
    public function executeAgreement(string $paymentId): AgreementResponse
    {
        $response = $this->makeRequest('POST', $this->getUrl('agreement/execute'), [
            'paymentID' => $paymentId,
        ]);

        if ($this->shouldLogTransactions) {
            $this->logTransaction('execute_agreement', $response);
        }

        $agreementResponse = new AgreementResponse($response);

        // Dispatch event if agreement was successful
        if ($agreementResponse->isSuccess()) {
            event(new AgreementCreated($agreementResponse));
        }

        return $agreementResponse;
    }

    /**
     * Query Agreement status
     *
     * @throws BkashException
     */
    public function queryAgreement(string $agreementId): AgreementResponse
    {
        $response = $this->makeRequest('POST', $this->getUrl('agreement/query'), [
            'agreementID' => $agreementId,
        ]);

        if ($this->shouldLogTransactions) {
            $this->logTransaction('query_agreement', $response);
        }

        return new AgreementResponse($response);
    }

    /**
     * Cancel Agreement
     *
     * @throws BkashException
     */
    public function cancelAgreement(string $agreementId): AgreementResponse
    {
        $response = $this->makeRequest('POST', $this->getUrl('agreement/cancel'), [
            'agreementID' => $agreementId,
        ]);

        if ($this->shouldLogTransactions) {
            $this->logTransaction('cancel_agreement', $response);
        }

        return new AgreementResponse($response);
    }

    /**
     * Create Payment (both regular and tokenized)
     *
     * @throws BkashException
     */
    public function createPayment(string $payerReference, float $amount, string $invoiceNumber, ?string $callbackURL = null, ?string $agreementId = null, ?string $merchantAssociationInfo = null): PaymentResponse
    {
        $callbackURL = $callbackURL ?? $this->config['callback_url'] ?? '';

        $this->validateParameters($payerReference, $invoiceNumber, $merchantAssociationInfo);

        $payload = [
            'mode' => $agreementId ? '0001' : '0011', // 0001 for tokenized, 0011 for regular
            'amount' => (string) $amount,
            'callbackURL' => $callbackURL,
            'payerReference' => $payerReference,
            'currency' => 'BDT',
            'merchantInvoiceNumber' => $invoiceNumber,
            'intent' => 'sale',
        ];

        // Add agreementID for tokenized payments
        if ($agreementId) {
            $payload['agreementID'] = $agreementId;
        }

        $response = $this->makeRequest('POST', $this->getUrl('create'), $payload);

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

        $paymentResponse = new PaymentResponse($response);

        // Dispatch appropriate event based on payment status
        if ($paymentResponse->isSuccess()) {
            if ($paymentResponse->isCompleted()) {
                event(new PaymentCompleted($paymentResponse));
            } elseif ($paymentResponse->isFailed()) {
                event(new PaymentFailed($paymentResponse));
            }
        }

        return $paymentResponse;
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
     * Query Payment status
     *
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

    /**
     * Search Transaction by trxID
     *
     * @throws BkashException
     */
    public function searchTransaction(string $trxId): SearchResponse
    {
        $response = $this->makeRequest('POST', $this->getUrl('search'), [
            'trxID' => $trxId,
        ]);

        if ($this->shouldLogTransactions) {
            $this->logTransaction('search_transaction', $response);
        }

        return new SearchResponse($response);
    }

    /**
     * Get Refund Status
     *
     * @throws BkashException
     */
    public function refundStatus(string $paymentId, string $refundId): RefundResponse
    {
        $response = $this->makeRequest('POST', $this->getUrl('refund/status'), [
            'paymentID' => $paymentId,
            'trxID' => $refundId,
        ]);

        if ($this->shouldLogTransactions) {
            $this->logTransaction('refund_status', $response);
        }

        return new RefundResponse($response);
    }

    /**
     * Log transaction to database if enabled
     */
    protected function logTransaction(string $type, array $response): void
    {
        if (! $this->shouldLogTransactions) {
            return;
        }

        try {
            BkashPayment::create([
                'type' => $type,
                'payment_id' => $response['paymentID'] ?? $response['agreementID'] ?? null,
                'trx_id' => $response['trxID'] ?? null,
                'amount' => $response['amount'] ?? null,
                'status' => $response['transactionStatus'] ?? $response['agreementStatus'] ?? ($response['statusCode'] === '0000' ? 'success' : 'failed'),
                'status_code' => $response['statusCode'] ?? null,
                'status_message' => $response['statusMessage'] ?? null,
                'response' => json_encode($response),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Log to Laravel log if database logging fails
            \Log::warning('Failed to log bKash transaction to database: '.$e->getMessage(), [
                'type' => $type,
                'response' => $response,
            ]);
        }
    }

    /**
     * @throws BkashException
     */
    /**
     * Make HTTP request to bKash API with proper error handling
     *
     * @throws BkashException
     */
    protected function makeRequest(string $method, string $url, array $data = []): array
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                $token = $this->getToken();

                $response = Http::withHeaders([
                    'Authorization' => $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                    ->timeout(30)
                    ->retry(2, 1000) // Retry 2 times with 1 second delay
                    ->{$method}($url, $data);

                if ($response->successful()) {
                    return $response->json();
                }

                // Handle token expiration
                if ($response->status() === 401 && $attempt < $maxRetries - 1) {
                    $this->clearTokenCache();
                    $attempt++;

                    continue;
                }

                // Parse error response
                $responseData = $response->json();
                $errorMessage = $responseData['errorMessage'] ?? $responseData['statusMessage'] ?? 'API request failed';
                $errorCode = $responseData['errorCode'] ?? $responseData['statusCode'] ?? $response->status();

                throw BkashException::fromApiResponse([
                    'errorMessage' => $errorMessage,
                    'errorCode' => $errorCode,
                    'statusCode' => $response->status(),
                ]);

            } catch (ConnectionException $e) {
                if ($attempt < $maxRetries - 1) {
                    $attempt++;
                    sleep(1); // Wait 1 second before retry

                    continue;
                }
                throw new BkashException('Network connection failed: '.$e->getMessage());
            } catch (BkashException $e) {
                throw $e;
            } catch (\Exception $e) {
                if ($attempt < $maxRetries - 1) {
                    $attempt++;

                    continue;
                }
                throw new BkashException('API request failed: '.$e->getMessage());
            }
        }

        throw new BkashException('Maximum retry attempts exceeded');
    }

    /**
     * Get cached token or generate new one
     *
     * @throws BkashException
     */
    protected function getToken(): string
    {
        return Cache::remember($this->tokenCacheKey, $this->tokenCacheTtl, function () {
            return $this->generateToken();
        });
    }

    /**
     * Clear token cache
     */
    protected function clearTokenCache(): void
    {
        Cache::forget($this->tokenCacheKey);
    }

    /**
     * Generate new access token
     *
     * @throws BkashException
     */
    protected function generateToken(): string
    {
        try {
            $credentials = $this->config['credentials'] ?? [];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'username' => $credentials['username'] ?? '',
                'password' => $credentials['password'] ?? '',
            ])
                ->timeout(30)
                ->post($this->getUrl('token'), [
                    'app_key' => $credentials['app_key'] ?? '',
                    'app_secret' => $credentials['app_secret'] ?? '',
                ]);

            if (! $response->successful()) {
                $error = $response->json();
                throw new BkashException(
                    'Token generation failed: '.($error['errorMessage'] ?? 'Unknown error'),
                    $error['errorCode'] ?? null,
                    $error['errorMessage'] ?? null
                );
            }

            $token = $response->json('id_token');
            if (! $token) {
                throw BkashException::tokenGenerationFailed();
            }

            return $token;
        } catch (ConnectionException $e) {
            throw new BkashException('Network error during token generation: '.$e->getMessage());
        } catch (BkashException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new BkashException('Token generation failed: '.$e->getMessage());
        }
    }

    /**
     * Refresh existing token
     *
     * @throws BkashException
     */
    protected function refreshToken(): string
    {
        try {
            $currentToken = Cache::get($this->tokenCacheKey);
            if (! $currentToken) {
                return $this->generateToken();
            }

            $credentials = $this->config['credentials'] ?? [];

            $response = Http::withHeaders([
                'Authorization' => $currentToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
                ->timeout(30)
                ->post($this->getUrl('token/refresh'), [
                    'app_key' => $credentials['app_key'] ?? '',
                    'app_secret' => $credentials['app_secret'] ?? '',
                ]);

            if (! $response->successful()) {
                // If refresh fails, generate new token
                $this->clearTokenCache();

                return $this->generateToken();
            }

            $newToken = $response->json('id_token');
            if (! $newToken) {
                $this->clearTokenCache();

                return $this->generateToken();
            }

            Cache::put($this->tokenCacheKey, $newToken, now()->addSeconds($this->tokenCacheTtl));

            return $newToken;
        } catch (\Exception $e) {
            $this->clearTokenCache();

            return $this->generateToken();
        }
    }

    /**
     * Get API URL for specific endpoint
     *
     * @throws BkashException
     */
    protected function getUrl(string $type): string
    {
        $mode = $this->config['sandbox'] ? 'sandbox' : 'production';
        $urls = $this->config['urls'][$mode] ?? [];

        if (! isset($urls[$type])) {
            throw new BkashException("API endpoint '{$type}' not found for {$mode} mode");
        }

        return $urls[$type];
    }

    /**
     * Get current configuration
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Check if in sandbox mode
     */
    public function isSandbox(): bool
    {
        return $this->config['sandbox'] ?? true;
    }

    /**
     * Check if transaction logging is enabled
     */
    public function isLoggingEnabled(): bool
    {
        return $this->shouldLogTransactions;
    }

    /**
     * Validate request parameters
     *
     * @throws BkashException
     */
    /**
     * Validate payer reference
     *
     * @throws BkashException
     */
    protected function validatePayerReference(string $payerReference): void
    {
        $invalidChars = ['<', '>', '&', '"', "'"];

        if (empty($payerReference)) {
            throw new BkashException('Payer reference cannot be empty');
        }

        if (strlen($payerReference) > 255) {
            throw new BkashException('Payer reference cannot exceed 255 characters');
        }

        if (strpbrk($payerReference, implode('', $invalidChars)) !== false) {
            throw new BkashException('Payer reference contains invalid characters: '.implode(', ', $invalidChars));
        }
    }

    /**
     * Validate request parameters
     *
     * @throws BkashException
     */
    protected function validateParameters(string $payerReference, string $invoiceNumber, ?string $merchantAssociationInfo = null): void
    {
        $this->validatePayerReference($payerReference);

        $invalidChars = ['<', '>', '&', '"', "'"];

        if (empty($invoiceNumber)) {
            throw new BkashException('Invoice number cannot be empty');
        }

        if (strlen($invoiceNumber) > 255) {
            throw new BkashException('Invoice number cannot exceed 255 characters');
        }

        if (strpbrk($invoiceNumber, implode('', $invalidChars)) !== false) {
            throw new BkashException('Invoice number contains invalid characters: '.implode(', ', $invalidChars));
        }

        if ($merchantAssociationInfo !== null) {
            if (strlen($merchantAssociationInfo) > 255) {
                throw new BkashException('Merchant association info cannot exceed 255 characters');
            }

            if (strpbrk($merchantAssociationInfo, implode('', $invalidChars)) !== false) {
                throw new BkashException('Merchant association info contains invalid characters: '.implode(', ', $invalidChars));
            }
        }
    }
}
