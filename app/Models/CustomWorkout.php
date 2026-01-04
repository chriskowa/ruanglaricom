<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomWorkout extends Model
{
    protected $fillable = [
        'runner_id',
        'workout_date',
        'type',
        'distance',
        'duration',
        'description',
        'notes',
        'workout_structure',
        'difficulty',
        'status',
        'completed_at',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'workout_date' => 'date',
            'distance' => 'decimal:2',
            'completed_at' => 'datetime',
            'workout_structure' => 'array',
        ];
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'runner_id');
    }
}
