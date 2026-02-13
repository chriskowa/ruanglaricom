<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventEmailDeliveryLog extends Model
{
    protected $fillable = [
        'event_id',
        'transaction_id',
        'context',
        'channel',
        'to',
        'status',
        'error_code',
        'error_message',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
