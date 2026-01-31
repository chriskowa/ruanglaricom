<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventEmailMinuteCounter extends Model
{
    protected $fillable = [
        'event_id',
        'minute_at',
        'reserved_emails',
    ];

    protected function casts(): array
    {
        return [
            'minute_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}

