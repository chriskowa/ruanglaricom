<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramSessionTracking extends Model
{
    protected $table = 'program_session_tracking';

    protected $fillable = [
        'enrollment_id',
        'session_day',
        'status',
        'rescheduled_date',
        'completed_at',
        'strava_link',
        'notes',
        'coach_feedback',
        'coach_rating',
        'rpe',
        'feeling',
    ];

    protected function casts(): array
    {
        return [
            'session_day' => 'integer',
            'rescheduled_date' => 'date',
            'completed_at' => 'datetime',
            'coach_rating' => 'integer',
            'rpe' => 'integer',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(ProgramEnrollment::class);
    }
}
