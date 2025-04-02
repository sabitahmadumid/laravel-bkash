<?php

namespace SabitAhmad\Bkash\Responses;

use Illuminate\Support\Carbon;

class PaymentResponse extends BaseResponse
{
    public function getPaymentId(): ?string
    {
        return $this->data['paymentID'] ?? null;
    }

    public function getPaymentExecuteTime(): ?Carbon
    {
        return isset($this->data['paymentExecuteTime'])
            ? Carbon::parse($this->data['paymentExecuteTime'])
            : null;
    }

    // Common methods
    public function getAmount(): ?float
    {
        return isset($this->data['amount'])
            ? (float) $this->data['amount']
            : null;
    }

    public function getPaymentUrl(): ?string
    {
        return $this->data['bkashURL'] ?? null;
    }

    public function getTrxId(): ?string
    {
        return $this->data['trxID'] ?? null; // Only in execute response
    }

    public function getCustomerMsisdn(): ?string
    {
        return $this->data['customerMsisdn'] ?? null;
    }

    public function getTransactionStatus(): ?string
    {
        return $this->data['transactionStatus'] ?? null;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->data['merchantInvoiceNumber'] ?? null;
    }
}
