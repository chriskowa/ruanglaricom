<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSubmission extends Model
{
    protected $fillable = [
        'status',
        'event_name',
        'banner',
        'event_date',
        'start_time',
        'location_name',
        'location_address',
        'city_id',
        'city_text',
        'race_type_id',
        'race_distance_ids',
        'registration_link',
        'social_media_link',
        'organizer_name',
        'organizer_contact',
        'contributor_name',
        'contributor_email',
        'contributor_phone',
        'notes',
        'fingerprint',
        'ip_hash',
        'ua_hash',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'event_date' => 'date',
        'race_distance_ids' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function raceType(): BelongsTo
    {
        return $this->belongsTo(RaceType::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function getBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'bg-green-900/30 text-green-300 border-green-500/30',
            'rejected' => 'bg-red-900/30 text-red-300 border-red-500/30',
            default => 'bg-yellow-900/30 text-yellow-300 border-yellow-500/30',
        };
    }
}
