<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RunThreadReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'run_thread_id',
        'reporter_id',
        'reason',
        'description',
        'status',
    ];

    public function runThread()
    {
        return $this->belongsTo(RunThread::class, 'run_thread_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
