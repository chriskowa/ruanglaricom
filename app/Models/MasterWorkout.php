<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MasterWorkout extends Model
{
    protected $fillable = [
        'type',
        'title',
        'description',
        'default_distance',
        'default_duration',
        'intensity',
        'coach_id',
        'is_public',
    ];

    protected $casts = [
        'default_distance' => 'decimal:2',
        'is_public' => 'boolean',
    ];

    public function coach()
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function visibilityLogs()
    {
        return $this->hasMany(WorkoutVisibilityLog::class);
    }

    /**
     * Scope a query to only include visible workouts for a user.
     */
    public function scopeVisibleFor(Builder $query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->whereNull('coach_id') // System workouts
              ->orWhere('is_public', true) // Public workouts
              ->orWhere('coach_id', $user->id); // Own workouts
        });
    }
}
