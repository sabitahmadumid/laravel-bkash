<?php

namespace SabitAhmad\Bkash\Exceptions;

use Exception;

class BkashException extends Exception
{
    /**
     * Official bKash API Error Code Mapping
     */
    public const ERROR_CODES = [
        '2001' => 'Invalid App Key',
        '2002' => 'Invalid Payment ID',
        '2003' => 'Process failed',
        '2004' => 'Invalid firstPaymentDate',
        '2005' => 'Invalid frequency',
        '2006' => 'Invalid amount',
        '2007' => 'Invalid currency',
        '2008' => 'Invalid intent',
        '2009' => 'Invalid Wallet',
        '2010' => 'Invalid OTP',
        '2011' => 'Invalid PIN',
        '2012' => 'Invalid Receiver MSISDN',
        '2013' => 'Resend Limit Exceeded',
        '2014' => 'Wrong PIN',
        '2015' => 'Wrong PIN count exceeded',
        '2016' => 'Wrong verification code',
        '2017' => 'Wrong verification limit exceeded',
        '2018' => 'OTP verification time expired',
        '2019' => 'PIN verification time expired',
        '2020' => 'Exception Occurred',
        '2021' => 'Invalid Mandate ID',
        '2022' => 'The mandate does not exist',
        '2023' => 'Insufficient Balance',
        '2024' => 'Exception occurred',
        '2025' => 'Invalid request body',
        '2026' => 'The reversal amount cannot be greater than the original transaction amount',
        '2027' => 'The mandate corresponding to the payer reference number already exists and cannot be created again',
        '2028' => 'Reverse failed because the transaction serial number does not exist',
        '2029' => 'Duplicate for all transactions',
        '2030' => 'Invalid mandate request type',
        '2031' => 'Invalid merchant invoice number',
        '2032' => 'Invalid transfer type',
        '2033' => 'Transaction not found',
        '2034' => 'The transaction cannot be reversed because the original transaction has been reversed',
        '2035' => 'Reverse failed because the initiator has no permission to reverse the transaction',
        '2036' => 'The direct debit mandate is not in Active state',
        '2037' => 'The account of the debit party is in a state which prohibits execution of this transaction',
        '2038' => 'Debit party identity tag prohibits execution of this transaction',
        '2039' => 'The account of the credit party is in a state which prohibits execution of this transaction',
        '2040' => 'Credit party identity tag prohibits execution of this transaction',
        '2041' => 'Credit party identity is in a state which does not support the current service',
        '2042' => 'Reverse failed because the initiator has no permission to reverse the transaction.',
        '2043' => 'The security credential of the subscriber is incorrect.',
        '2044' => 'Identity has not subscribed to a product that contains the expected service or the identity is not in Active status.',
        '2045' => 'The MSISDN of the customer does not exist.',
        '2046' => 'Identity has not subscribed to a product that contains requested service.',
        '2047' => 'TLV Data Format Error',
        '2048' => 'Invalid Payer Reference',
        '2049' => 'Invalid Merchant Callback URL',
        '2050' => 'Agreement already exists between payer and merchant',
        '2051' => 'Invalid Agreement ID',
        '2052' => 'Agreement is in incomplete state',
        '2053' => 'Agreement has already been cancelled',
        '2054' => "Agreement execution pre-requisite hasn't been met",
        '2055' => 'Invalid Agreement State',
        '2056' => 'Invalid Payment State',
        '2057' => 'Not a bKash Account',
        '2058' => 'Not a Customer Wallet',
        '2059' => 'Multiple OTP request for a single session denied',
        '2060' => "Payment execution pre-requisite hasn't been met",
        '2061' => 'This action can only be performed by the agreement or payment initiator party',
        '2062' => 'The payment has already been completed',
        '2063' => 'Mode is not valid as per request data',
        '2064' => 'This product mode currently unavailable',
        '2065' => 'Mendatory field missing',
        '2066' => 'Agreement is not shared with other merchant',
        '2067' => 'Invalid permission',
        '2068' => 'Transaction has already been completed',
        '2069' => 'Transaction has already been cancelled',
        '2071' => 'Refund after allowed days not permitted',
        '2072' => 'Refund amount not valid',
        '2073' => 'Invalid SKU',
        '2074' => 'The transaction cannot be reversed',
        '2075' => 'SKU Character Limit Exceeded',
        '2076' => 'Reason Character Limit Exceeded',
        '2077' => 'Invalid TrxID',
        '2078' => 'Invalid Reason',
        '2079' => 'Invalid app Token',
        '2080' => 'The identity is not permitted to initiate this transaction',
        '2081' => 'The identity of the debit or credit party is in a state which prohibits the execution of this transaction',
        '2082' => 'The merchant is not permitted to initiate this transaction',
        '2116' => 'The agreement execution has already been completed',
        '2117' => 'The payment execution has already been completed',
        '2118' => 'The Platform value is invalid',
        '2119' => 'The authorized payment has already been processed',
        '2127' => 'Transaction not yet completed',
        '503' => 'System is undergoing maintenance. Please try again later',
    ];

    /**
     * The error code returned by the bKash API.
     */
    protected ?string $errorCode;

    /**
     * The error message returned by the bKash API.
     */
    protected ?string $errorMessage;

    /**
     * Create a new BkashException instance.
     */
    public function __construct(
        string $message = 'An error occurred while processing the bKash request.',
        ?string $errorCode = null,
        ?string $errorMessage = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the error code returned by the bKash API.
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Get the error message returned by the bKash API.
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Get the official bKash error description for this error code.
     */
    public function getOfficialDescription(): ?string
    {
        return $this->errorCode ? (self::ERROR_CODES[$this->errorCode] ?? null) : null;
    }

    /**
     * Create a BkashException from an API response.
     *
     * @return static
     */
    public static function fromApiResponse(array $response): self
    {
        $errorCode = $response['errorCode'] ?? $response['statusCode'] ?? null;
        $errorMessage = $response['errorMessage'] ?? $response['statusMessage'] ?? null;

        if (! $errorMessage && $errorCode && isset(self::ERROR_CODES[$errorCode])) {
            $errorMessage = self::ERROR_CODES[$errorCode];
        }

        return new static(
            "bKash API Error: {$errorMessage} (Code: {$errorCode})",
            $errorCode,
            $errorMessage
        );
    }

    /**
     * Create a BkashException for token generation failures.
     *
     * @return static
     */
    public static function tokenGenerationFailed(): self
    {
        return new static('Failed to generate bKash token.');
    }

    /**
     * Create a BkashException for token refresh failures.
     *
     * @return static
     */
    public static function tokenRefreshFailed(): self
    {
        return new static('Failed to refresh bKash token.');
    }

    /**
     * Create a BkashException for invalid callback data.
     *
     * @return static
     */
    public static function invalidCallbackData(): self
    {
        return new static('Invalid callback data received from bKash.');
    }

    /**
     * Create a BkashException for network errors.
     *
     * @return static
     */
    public static function networkError(string $message = ''): self
    {
        return new static('Network error occurred while connecting to bKash: '.$message);
    }

    /**
     * Create a BkashException for configuration errors.
     *
     * @return static
     */
    public static function configurationError(string $message = ''): self
    {
        return new static('bKash configuration error: '.$message);
    }

    /**
     * Create a BkashException for validation errors.
     *
     * @return static
     */
    public static function validationError(string $field, string $message = ''): self
    {
        return new static("Validation error for field '{$field}': ".$message);
    }

    /**
     * Create a BkashException for invalid App Key.
     *
     * @return static
     */
    public static function invalidAppKey(): self
    {
        return new static('Invalid App Key.', '2001', self::ERROR_CODES['2001']);
    }

    /**
     * Create a BkashException for invalid payment ID.
     *
     * @return static
     */
    public static function invalidPaymentId(): self
    {
        return new static('Invalid Payment ID.', '2002', self::ERROR_CODES['2002']);
    }

    /**
     * Create a BkashException for insufficient balance (Error 2023).
     *
     * @return static
     */
    public static function insufficientBalance(): self
    {
        return new static('Insufficient balance in bKash account.', '2023', self::ERROR_CODES['2023']);
    }

    /**
     * Create a BkashException for verification expiration (Error 2018/2019).
     *
     * @return static
     */
    public static function transactionExpired(string $code = '2018'): self
    {
        $message = self::ERROR_CODES[$code] ?? 'Verification time expired.';

        return new static($message, $code, $message);
    }

    /**
     * Create a BkashException for duplicate transactions (Error 2029).
     *
     * @return static
     */
    public static function duplicateTransaction(): self
    {
        return new static('Duplicate transaction detected.', '2029', self::ERROR_CODES['2029']);
    }

    /**
     * Create a BkashException for cancelled transactions (Error 2069).
     *
     * @return static
     */
    public static function transactionCancelled(): self
    {
        return new static('Transaction has already been cancelled.', '2069', self::ERROR_CODES['2069']);
    }

    /**
     * Create a BkashException for cancelled agreements (Error 2053).
     *
     * @return static
     */
    public static function agreementCancelled(): self
    {
        return new static('Agreement has already been cancelled.', '2053', self::ERROR_CODES['2053']);
    }

    /**
     * Create a BkashException for system maintenance (Error 503).
     *
     * @return static
     */
    public static function systemMaintenance(): self
    {
        return new static('System is undergoing maintenance. Please try again later.', '503', self::ERROR_CODES['503']);
    }

    /**
     * Check if this is a network-related error.
     */
    public function isNetworkError(): bool
    {
        return str_contains(strtolower($this->getMessage()), 'network') ||
               str_contains(strtolower($this->getMessage()), 'connection') ||
               str_contains(strtolower($this->getMessage()), 'timeout');
    }

    /**
     * Check if this is a validation error.
     */
    public function isValidationError(): bool
    {
        return str_contains(strtolower($this->getMessage()), 'validation') ||
               str_contains(strtolower($this->getMessage()), 'invalid');
    }

    /**
     * Check if this is a token-related error.
     */
    public function isTokenError(): bool
    {
        return str_contains(strtolower($this->getMessage()), 'token') ||
               $this->getCode() === 401;
    }

    /**
     * Check if this is an insufficient balance error (Code 2023).
     */
    public function isInsufficientBalance(): bool
    {
        return $this->errorCode === '2023';
    }

    /**
     * Check if this is a cancelled transaction or agreement (Code 2069 or 2053).
     */
    public function isCancelled(): bool
    {
        return in_array($this->errorCode, ['2069', '2053']);
    }

    /**
     * Check if this is a duplicate transaction error (Code 2029).
     */
    public function isDuplicate(): bool
    {
        return $this->errorCode === '2029';
    }

    /**
     * Check if the payment/agreement was already completed (Codes 2062, 2068, 2116, 2117, 2119).
     */
    public function isAlreadyCompleted(): bool
    {
        return in_array($this->errorCode, ['2062', '2068', '2116', '2117', '2119']);
    }

    /**
     * Check if bKash system is in maintenance (Code 503).
     */
    public function isMaintenance(): bool
    {
        return $this->errorCode === '503';
    }

    /**
     * Check if this is a refund-specific error.
     */
    public function isRefundError(): bool
    {
        return in_array($this->errorCode, [
            '2071', '2072', '2073', '2074', '2075', '2076',
            '2077', '2078', '2079', '2080', '2081', '2082', '2127',
        ]);
    }
}
