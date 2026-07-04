<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunThreadParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'run_thread_id',
        'user_id',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function runThread()
    {
        return $this->belongsTo(RunThread::class, 'run_thread_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
