<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkoutVisibilityLog extends Model
{
    protected $fillable = [
        'master_workout_id',
        'user_id',
        'old_visibility',
        'new_visibility',
    ];

    protected $casts = [
        'old_visibility' => 'boolean',
        'new_visibility' => 'boolean',
    ];

    public function masterWorkout()
    {
        return $this->belongsTo(MasterWorkout::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
