<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventEmailCampaign extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'type',
        'preset_template',
        'subject',
        'content',
        'offset_days',
        'send_time',
        'send_at',
        'filters',
        'status',
        'target_count',
        'sent_count',
    ];

    protected $casts = [
        'content' => 'array',
        'filters' => 'array',
        'send_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(EventEmailDelivery::class);
    }
}
