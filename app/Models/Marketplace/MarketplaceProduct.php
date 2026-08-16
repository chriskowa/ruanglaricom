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
        'is_approved' => 'boolean',
        'is_featured' => 'boolean',
        'is_sold' => 'boolean',
        'is_archived' => 'boolean',
        'sold_at' => 'datetime',
        'archived_at' => 'datetime',
        'featured_until' => 'datetime',
        'boosted_at' => 'datetime',
        'auction_start_at' => 'datetime',
        'auction_end_at' => 'datetime',
    ];

    public function isFeaturedActive(): bool
    {
        if (!$this->is_featured) {
            return false;
        }
        if ($this->featured_until && now()->gt($this->featured_until)) {
            return false;
        }
        return true;
    }

    public function scopePublicListing($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('is_archived')->orWhere('is_archived', false);
            })
            ->where(function ($q) {
                $q->whereNull('is_approved')->orWhere('is_approved', true);
            });
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function soldToUser()
    {
        return $this->belongsTo(User::class, 'sold_to_user_id');
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
