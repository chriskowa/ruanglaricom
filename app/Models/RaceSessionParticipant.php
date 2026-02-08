<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RaceSessionParticipant extends Model
{
    protected $fillable = [
        'race_id',
        'participant_id',
        'bib_number',
        'name',
        'predicted_time_ms',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function laps(): HasMany
    {
        return $this->hasMany(RaceSessionLap::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(RaceCertificate::class, 'race_session_participant_id');
    }
}
