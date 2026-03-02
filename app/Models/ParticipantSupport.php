<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantSupport extends Model
{
    protected $fillable = [
        'participant_id',
        'supporter_name',
        'supporter_phone',
        'nominal',
        'status',
        'snap_token',
        'midtrans_order_id',
        'payment_proof',
        'payment_method',
        'payment_channel',
        'unique_code',
        'moota_transaction_id',
        'expires_at',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'unique_code' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
