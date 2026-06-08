<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramEnrollment extends Model
{
    protected $fillable = [
        'program_id',
        'runner_id',
        'start_date',
        'end_date',
        'status',
        'payment_status',
        'payment_transaction_id',
        'current_vdot',
        'target_race_date',
        'status_reason',
        'reschedule_history',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'target_race_date' => 'date',
            'reschedule_history' => 'array',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'runner_id');
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(WalletTransaction::class, 'payment_transaction_id');
    }

    public function injuryLogs()
    {
        return $this->hasMany(RunnerInjuryLog::class, 'enrollment_id');
    }

    public function weeklyReports()
    {
        return $this->hasMany(ProgramWeeklyReport::class, 'enrollment_id');
    }
}
