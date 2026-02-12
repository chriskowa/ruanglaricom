<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceCertificate extends Model
{
    protected $fillable = [
        'race_id',
        'race_session_id',
        'race_session_participant_id',
        'participant_id',
        'final_position',
        'total_time_ms',
        'pdf_path',
        'created_by',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
