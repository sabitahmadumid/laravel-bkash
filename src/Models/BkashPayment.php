<?php

namespace SabitAhmad\Bkash\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BkashPayment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'response' => 'array',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Query scopes for common filtering
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentId(Builder $query, string $paymentId): Builder
    {
        return $query->where('payment_id', $paymentId);
    }

    public function scopeByTrxId(Builder $query, string $trxId): Builder
    {
        return $query->where('trx_id', $trxId);
    }

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', 'success')
            ->orWhere('status', 'Completed');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed')
            ->orWhere('status', 'Failed');
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    // Helper methods
    public function isSuccessful(): bool
    {
        return in_array($this->status, ['success', 'Completed']);
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'Failed']);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getResponseData(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->response;
        }

        return $this->response[$key] ?? $default;
    }
}
