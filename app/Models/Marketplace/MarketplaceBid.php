<?php

namespace App\Models\Marketplace;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MarketplaceBid extends Model
{
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(MarketplaceProduct::class, 'product_id');
    }

    public function bidder()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

