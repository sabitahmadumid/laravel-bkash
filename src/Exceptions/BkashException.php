<?php

namespace SabitAhmad\Bkash\Exceptions;

use Exception;

class BkashException extends Exception
{
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
     * Create a BkashException from an API response.
     *
     * @return static
     */
    public static function fromApiResponse(array $response): self
    {
        $errorCode = $response['errorCode'] ?? null;
        $errorMessage = $response['errorMessage'] ?? null;

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
}
