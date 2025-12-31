<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaderboardStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'active_days',
        'percentage',
        'streak',
        'qualified',
        'last_active_date',
        'old_pb',
        'new_pb',
        'gap_seconds',
        'gap',
        'pace',
    ];

    protected $casts = [
        'qualified' => 'boolean',
        'last_active_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
