<?php

namespace App\Models\Marketplace;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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
}
