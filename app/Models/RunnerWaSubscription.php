<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RunnerWaSubscription extends Model
{
    use HasFactory;

    protected $table = 'runner_wa_subscriptions';

    protected $fillable = [
        'user_id',
        'order_id',
        'amount',
        'status',
        'snap_token',
        'payment_method',
        'midtrans_transaction_status',
        'paid_at',
        'subscribed_from',
        'subscribed_until',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'subscribed_from' => 'datetime',
        'subscribed_until' => 'datetime',
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isActive(): bool
    {
        return $this->isPaid() && $this->subscribed_until && $this->subscribed_until->isFuture();
    }
}
