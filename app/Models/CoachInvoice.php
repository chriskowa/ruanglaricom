<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class CoachInvoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'coach_id',
        'runner_id',
        'program_id',
        'enrollment_id',
        'pricing_type',
        'amount',
        'platform_fee',
        'net_coach_amount',
        'quantity',
        'period_start',
        'period_end',
        'due_date',
        'payment_status',
        'payment_method',
        'payment_proof',
        'paid_at',
        'verified_by_coach',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'net_coach_amount' => 'decimal:2',
            'quantity' => 'integer',
            'period_start' => 'date',
            'period_end' => 'date',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'verified_by_coach' => 'boolean',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'runner_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class, 'program_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(ProgramEnrollment::class, 'enrollment_id');
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(int $coachId): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        return "INV-C{$coachId}-{$date}-{$random}";
    }

    /**
     * Get label for pricing model
     */
    public function getPricingLabelAttribute(): string
    {
        return match ($this->pricing_type) {
            'hourly' => 'Per Jam / Sesi',
            'daily' => 'Harian (Daily Pass)',
            'weekly' => 'Mingguan (Weekly)',
            'monthly' => 'Bulanan (Monthly Retainer)',
            default => 'Paket Sekali Bayar',
        };
    }

    /**
     * Check if invoice is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->payment_status === 'paid') {
            return false;
        }

        return $this->due_date && Carbon::parse($this->due_date)->endOfDay()->isPast();
    }
}
