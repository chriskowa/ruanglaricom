<?php

namespace App\Models\Marketplace;

use Illuminate\Database\Eloquent\Model;

class MarketplaceCategory extends Model
{
    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(MarketplaceProduct::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(MarketplaceCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(MarketplaceCategory::class, 'parent_id');
    }

    public function brands()
    {
        return $this->belongsToMany(MarketplaceBrand::class, 'marketplace_brand_category');
    }
}
