<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventEmailDelivery extends Model
{
    protected $fillable = [
        'event_email_campaign_id',
        'participant_id',
        'to_email',
        'to_name',
        'status',
        'scheduled_at',
        'sent_at',
        'attempts',
        'error_message',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EventEmailCampaign::class, 'event_email_campaign_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
