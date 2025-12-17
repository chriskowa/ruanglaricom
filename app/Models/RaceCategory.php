<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceCategory extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'distance_km',
        'code',
        'quota',
        'min_age',
        'max_age',
        'cutoff_minutes',
        'price_early',
        'price_regular',
        'price_late',
        'reg_start_at',
        'reg_end_at',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'reg_start_at' => 'datetime',
        'reg_end_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get remaining quota
     */
    public function getRemainingQuota(): int
    {
        if (!$this->quota) {
            return 999999; // Unlimited
        }

        // Count registered participants
        $registeredCount = \App\Models\Participant::where('race_category_id', $this->id)
            ->whereHas('transaction', function($query) {
                $query->whereIn('payment_status', ['pending', 'paid']);
            })
            ->count();

        return max(0, $this->quota - $registeredCount);
    }

    /**
     * Get cache key for quota
     */
    public function getQuotaCacheKey(): string
    {
        return "category:quota:{$this->id}";
    }
}
