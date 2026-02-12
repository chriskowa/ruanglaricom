<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EoReportEmailDelivery extends Model
{
    protected $fillable = [
        'event_id',
        'eo_user_id',
        'triggered_by_user_id',
        'to_email',
        'to_name',
        'subject',
        'report_type',
        'filters',
        'queue',
        'status',
        'attempts',
        'first_attempt_at',
        'last_attempt_at',
        'sent_at',
        'failure_code',
        'failure_message',
        'provider_message_id',
        'failure_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'first_attempt_at' => 'datetime',
            'last_attempt_at' => 'datetime',
            'sent_at' => 'datetime',
            'failure_notified_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function eoUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'eo_user_id');
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }
}
