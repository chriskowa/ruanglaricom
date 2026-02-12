<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityInvoice extends Model
{
    protected $fillable = [
        'community_registration_id',
        'transaction_id',
        'payment_method',
        'status',
        'total_original',
        'discount_amount',
        'admin_fee',
        'unique_code',
        'final_amount',
        'qris_payload',
    ];

    protected $casts = [
        'total_original' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(CommunityRegistration::class, 'community_registration_id');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
