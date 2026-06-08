<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramWeeklyReport extends Model
{
    protected $fillable = [
        'enrollment_id',
        'week_number',
        'report_text',
        'status',
    ];

    public function enrollment()
    {
        return $this->belongsTo(ProgramEnrollment::class, 'enrollment_id');
    }
}
