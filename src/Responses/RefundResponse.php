<?php

namespace SabitAhmad\Bkash\Responses;

class RefundResponse extends BaseResponse
{
    public function getRefundId(): ?string
    {
        return $this->data['refundID'] ?? null;
    }

    public function getOriginalPaymentId(): ?string
    {
        return $this->data['originalPaymentID'] ?? null;
    }

    public function getRefundAmount(): ?float
    {
        return isset($this->data['amount']) ? (float) $this->data['amount'] : null;
    }
}
