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
        'pricing_type',
        'subscription_status',
        'current_period_start',
        'current_period_end',
        'next_billing_date',
        'total_sessions_quota',
        'sessions_used',
        'sessions_remaining',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'target_race_date' => 'date',
            'reschedule_history' => 'array',
            'current_period_start' => 'date',
            'current_period_end' => 'date',
            'next_billing_date' => 'date',
            'total_sessions_quota' => 'integer',
            'sessions_used' => 'integer',
            'sessions_remaining' => 'integer',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function invoices()
    {
        return $this->hasMany(CoachInvoice::class, 'enrollment_id');
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
