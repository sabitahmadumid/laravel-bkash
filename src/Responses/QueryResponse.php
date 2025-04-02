<?php

namespace SabitAhmad\Bkash\Responses;

class QueryResponse extends BaseResponse
{

    public function getPaymentId(): ?string
    {
        return $this->data['paymentID'] ?? null;
    }

    public function getTransactionStatus(): ?string
    {
        return $this->data['transactionStatus'] ?? null;
    }

    public function getAmount(): ?float
    {
        return isset($this->data['amount']) ? (float) $this->data['amount'] : null;
    }
}
