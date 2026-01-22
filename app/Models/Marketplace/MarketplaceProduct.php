<?php

namespace App\Models\Marketplace;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class MarketplaceProduct extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meta_data' => 'array',
        'is_active' => 'boolean',
        'auction_start_at' => 'datetime',
        'auction_end_at' => 'datetime',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(MarketplaceCategory::class, 'category_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(MarketplaceCategory::class, 'sub_category_id');
    }

    public function brand()
    {
        return $this->belongsTo(MarketplaceBrand::class, 'brand_id');
    }

    public function images()
    {
        return $this->hasMany(MarketplaceProductImage::class, 'product_id');
    }

    public function primaryImage()
    {
        return $this->hasOne(MarketplaceProductImage::class, 'product_id')->where('is_primary', true);
    }

    public function bids()
    {
        return $this->hasMany(MarketplaceBid::class, 'product_id');
    }

    public function latestBid()
    {
        return $this->hasOne(MarketplaceBid::class, 'product_id')->latestOfMany();
    }

    public function consignmentIntake()
    {
        return $this->hasOne(MarketplaceConsignmentIntake::class, 'product_id');
    }
}
