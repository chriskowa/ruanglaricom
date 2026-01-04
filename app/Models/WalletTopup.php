<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTopup extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'payment_method',
        'midtrans_order_id',
        'midtrans_transaction_status',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mark topup as paid
     */
    public function markAsPaid(string $midtransOrderId, string $transactionStatus): void
    {
        $this->update([
            'status' => 'success',
            'midtrans_order_id' => $midtransOrderId,
            'midtrans_transaction_status' => $transactionStatus,
        ]);
    }

    /**
     * Mark topup as failed
     */
    public function markAsFailed(?string $midtransOrderId = null, ?string $transactionStatus = null): void
    {
        $this->update([
            'status' => 'failed',
            'midtrans_order_id' => $midtransOrderId ?? $this->midtrans_order_id,
            'midtrans_transaction_status' => $transactionStatus ?? $this->midtrans_transaction_status,
        ]);
    }
}
