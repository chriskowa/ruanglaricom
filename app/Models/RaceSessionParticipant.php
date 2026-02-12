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
        'result_time_ms',
        'finished_at',
        'created_at',
    ];

    protected $casts = [
        'finished_at' => 'datetime',
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

    public function getFormattedPredictedTimeAttribute(): string
    {
        $ms = $this->predicted_time_ms;
        if ($ms === null) {
            return '-';
        }

        $ms = max(0, (int) $ms);
        $cs = (int) floor(($ms % 1000) / 10);
        $totalSeconds = (int) floor($ms / 1000);
        $minutes = intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%d:%02d.%02d', $minutes, $seconds, $cs);
    }

    public function getFormattedResultTimeAttribute(): string
    {
        $ms = $this->result_time_ms;
        if ($ms === null) {
            return '-';
        }

        $ms = max(0, (int) $ms);
        $cs = (int) floor(($ms % 1000) / 10);
        $totalSeconds = (int) floor($ms / 1000);
        $minutes = intdiv($totalSeconds, 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%d:%02d.%02d', $minutes, $seconds, $cs);
    }
}
