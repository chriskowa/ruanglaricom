<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChallengeActivity extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'distance',
        'duration_seconds',
        'image_path',
        'strava_link',
        'status',
        'rejection_reason',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
