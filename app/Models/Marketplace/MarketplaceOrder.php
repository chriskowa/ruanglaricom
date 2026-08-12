<?php

namespace App\Models\Marketplace;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MarketplaceOrder extends Model
{
    protected $guarded = [];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items()
    {
        return $this->hasMany(MarketplaceOrderItem::class, 'order_id');
    }

    public function getGrandTotalAttribute()
    {
        return (float) ($this->attributes['total_amount'] ?? 0);
    }

    public function getSubtotalAttribute()
    {
        return max(0, (float) ($this->attributes['total_amount'] ?? 0) - (float) ($this->attributes['shipping_cost'] ?? 0));
    }
}
