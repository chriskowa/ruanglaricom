<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceSessionLap extends Model
{
    protected $fillable = [
        'race_id',
        'race_session_id',
        'race_session_participant_id',
        'participant_id',
        'lap_number',
        'lap_time_ms',
        'total_time_ms',
        'delta_ms',
        'position',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(RaceSession::class, 'race_session_id');
    }

    public function raceSessionParticipant(): BelongsTo
    {
        return $this->belongsTo(RaceSessionParticipant::class, 'race_session_participant_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
