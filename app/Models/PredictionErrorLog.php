<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PredictionErrorLog extends Model
{
    protected $fillable = [
        'event_id',
        'race_category_id',
        'context',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function raceCategory(): BelongsTo
    {
        return $this->belongsTo(RaceCategory::class);
    }
}
