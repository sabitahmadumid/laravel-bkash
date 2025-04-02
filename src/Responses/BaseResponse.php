<?php

namespace SabitAhmad\Bkash\Responses;

class BaseResponse
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getRawData(): array
    {
        return $this->data;
    }

    public function isSuccess(): bool
    {
        return isset($this->data['statusCode']) && $this->data['statusCode'] === '0000';
    }

    public function getErrorMessage(): ?string
    {
        return $this->data['errorMessage'] ?? null;
    }
}
