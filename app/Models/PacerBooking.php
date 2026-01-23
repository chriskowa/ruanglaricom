<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacerBooking extends Model
{
    protected $fillable = [
        'invoice_number',
        'runner_id',
        'pacer_id',
        'event_name',
        'race_date',
        'distance',
        'target_pace',
        'meeting_point',
        'notes',
        'total_amount',
        'platform_fee_amount',
        'pacer_amount',
        'status',
        'midtrans_order_id',
        'snap_token',
        'paid_at',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
        'disputed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'race_date' => 'date',
            'total_amount' => 'decimal:2',
            'platform_fee_amount' => 'decimal:2',
            'pacer_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'disputed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'runner_id');
    }

    public function pacer(): BelongsTo
    {
        return $this->belongsTo(Pacer::class);
    }
}

