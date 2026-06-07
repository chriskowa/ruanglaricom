<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RunnerInjuryLog extends Model
{
    protected $fillable = [
        'user_id',
        'enrollment_id',
        'injury_type',
        'body_part',
        'injured_at',
        'recovered_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'injured_at' => 'date',
            'recovered_at' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(ProgramEnrollment::class);
    }
}
