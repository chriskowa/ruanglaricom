<?php

namespace App\Models\Marketplace;

use Illuminate\Database\Eloquent\Model;

class MarketplaceOrderItem extends Model
{
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(MarketplaceProduct::class, 'product_id');
    }
}
