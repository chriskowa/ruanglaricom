<?php

namespace App\Models\Marketplace;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MarketplaceConsignmentIntake extends Model
{
    protected $guarded = [];

    protected $casts = [
        'received_at' => 'datetime',
        'listed_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(MarketplaceProduct::class, 'product_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
