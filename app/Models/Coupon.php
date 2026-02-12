<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'event_id',
        'code',
        'type',
        'value',
        'min_transaction_amount',
        'max_uses',
        'usage_limit_per_user',
        'used_count',
        'start_at',
        'expires_at',
        'is_active',
        'is_stackable',
        'applicable_categories',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_transaction_amount' => 'decimal:2',
        'max_uses' => 'integer',
        'usage_limit_per_user' => 'integer',
        'used_count' => 'integer',
        'start_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'is_stackable' => 'boolean',
        'applicable_categories' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Check if coupon is valid
     */
    public function isValid(?int $eventId = null, ?float $transactionAmount = null, ?int $userId = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->start_at && $this->start_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        // Check Event Scope
        if ($this->event_id && $eventId && $this->event_id != $eventId) {
            return false;
        }

        // Check Minimum Transaction Amount
        if ($transactionAmount !== null && $this->min_transaction_amount > 0 && $transactionAmount < $this->min_transaction_amount) {
            return false;
        }

        // Simple usage check (detailed check with pending transactions should happen with locking)
        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        // Check Usage Limit Per User
        if ($this->usage_limit_per_user && $userId) {
            $userUsage = $this->transactions()
                ->where('user_id', $userId)
                ->whereIn('payment_status', ['paid', 'pending'])
                ->count();

            if ($userUsage >= $this->usage_limit_per_user) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if coupon can be used
     */
    public function canBeUsed(?int $eventId = null, ?float $transactionAmount = null, ?int $userId = null): bool
    {
        return $this->isValid($eventId, $transactionAmount, $userId);
    }

    /**
     * Apply discount to amount
     */
    public function applyDiscount(float $amount): float
    {
        if ($this->type === 'percent') {
            return $amount * ($this->value / 100);
        }

        // Fixed discount
        return min($this->value, $amount);
    }
}
