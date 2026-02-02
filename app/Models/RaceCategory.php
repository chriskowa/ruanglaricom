<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceCategory extends Model
{
    protected $fillable = [
        'event_id',
        'master_gpx_id',
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
        'prizes',
        'early_bird_quota',
        'early_bird_end_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'reg_start_at' => 'datetime',
        'reg_end_at' => 'datetime',
        'early_bird_end_at' => 'datetime',
        'prizes' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function masterGpx(): BelongsTo
    {
        return $this->belongsTo(MasterGpx::class);
    }

    public function participants()
    {
        return $this->hasMany(Participant::class, 'race_category_id');
    }

    /**
     * Get remaining quota
     */
    public function getRemainingQuota(): int
    {
        if (! $this->quota) {
            return 999999; // Unlimited
        }

        // Count registered participants
        $registeredCount = \App\Models\Participant::where('race_category_id', $this->id)
            ->whereHas('transaction', function ($query) {
                $query->whereIn('payment_status', ['paid', 'cod']);
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

    /**
     * Get COT in hours
     */
    public function getCotHoursAttribute()
    {
        return $this->cutoff_minutes ? round($this->cutoff_minutes / 60, 1) : null;
    }
}
