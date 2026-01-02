<?php

namespace App\Models\Marketplace;

use Illuminate\Database\Eloquent\Model;

class MarketplaceBrand extends Model
{
    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(MarketplaceProduct::class, 'brand_id');
    }

    public function categories()
    {
        return $this->belongsToMany(MarketplaceCategory::class, 'marketplace_brand_category');
    }
}
