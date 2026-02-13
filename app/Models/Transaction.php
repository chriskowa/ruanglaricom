<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Transaction extends Model
{
    protected $table = 'transactions';

    protected $fillable = [
        'public_ref',
        'event_id',
        'user_id',
        'pic_data',
        'total_original',
        'coupon_id',
        'discount_amount',
        'admin_fee',
        'final_amount',
        'payment_status',
        'payment_gateway',
        'midtrans_mode',
        'unique_code',
        'payment_channel',
        'snap_token',
        'midtrans_order_id',
        'midtrans_transaction_status',
        'paid_at',
        'pending_reminder_last_sent_at',
        'pending_reminder_count',
        'pending_reminder_last_channel',
    ];

    protected $casts = [
        'pic_data' => 'array',
        'total_original' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'pending_reminder_last_sent_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $transaction) {
            if (! empty($transaction->public_ref)) {
                return;
            }

            do {
                $ref = 'RL'.Str::upper(Str::random(10));
            } while (self::query()->where('public_ref', $ref)->exists());

            $transaction->public_ref = $ref;
        });
    }

    /**
     * Mark transaction as paid
     */
    public function markAsPaid(?string $midtransOrderId = null, ?string $transactionStatus = null): void
    {
        $this->update([
            'payment_status' => 'paid',
            'midtrans_order_id' => $midtransOrderId ?? $this->midtrans_order_id,
            'midtrans_transaction_status' => $transactionStatus ?? $this->midtrans_transaction_status,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(?string $midtransOrderId = null, ?string $transactionStatus = null): void
    {
        $this->update([
            'payment_status' => 'failed',
            'midtrans_order_id' => $midtransOrderId ?? $this->midtrans_order_id,
            'midtrans_transaction_status' => $transactionStatus ?? $this->midtrans_transaction_status,
        ]);
    }
}
