<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunThreadMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'run_thread_id',
        'user_id',
        'message',
    ];

    public function thread()
    {
        return $this->belongsTo(RunThread::class, 'run_thread_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
