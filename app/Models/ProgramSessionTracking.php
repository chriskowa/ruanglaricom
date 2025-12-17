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
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'session_day' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(ProgramEnrollment::class);
    }
}
