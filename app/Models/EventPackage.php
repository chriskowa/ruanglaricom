<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventPackage extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'price',
        'quota',
        'sold_count',
        'is_sold_out',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quota' => 'integer',
        'sold_count' => 'integer',
        'is_sold_out' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    /**
     * Get remaining quota
     */
    public function getRemainingQuota(): int
    {
        return max(0, $this->quota - $this->sold_count);
    }

    /**
     * Get cache key for quota
     */
    public function getQuotaCacheKey(): string
    {
        return "package:quota:{$this->id}";
    }
}
