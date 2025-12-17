<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'short_description',
        'full_description',
        'start_at',
        'end_at',
        'location_name',
        'location_address',
        'location_lat',
        'location_lng',
        'slug',
        'hero_image_url',
        'hero_image',
        'logo_image',
        'floating_image',
        'medal_image',
        'jersey_image',
        'map_embed_url',
        'google_calendar_url',
        'registration_open_at',
        'registration_close_at',
        'promo_code',
        'facilities',
        'jersey_sizes',
        'gallery',
        'theme_colors',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'registration_open_at' => 'datetime',
        'registration_close_at' => 'datetime',
        'facilities' => 'array',
        'jersey_sizes' => 'array',
        'gallery' => 'array',
        'theme_colors' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->name);
            }
        });

        static::updating(function ($event) {
            if (empty($event->slug) && $event->isDirty('name')) {
                $event->slug = Str::slug($event->name);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(RaceCategory::class);
    }

    public function raceResults(): HasMany
    {
        return $this->hasMany(RaceResult::class);
    }

    /**
     * Get cache key for event detail
     */
    public function getCacheKey(): string
    {
        return "event:detail:{$this->slug}";
    }

    /**
     * Get hero image URL (prioritize uploaded image over URL)
     */
    public function getHeroImageUrl()
    {
        if (!empty($this->attributes['hero_image'])) {
            return asset('storage/' . $this->attributes['hero_image']);
        }
        // Fallback to hero_image_url if no uploaded image
        return $this->attributes['hero_image_url'] ?? null;
    }

    /**
     * Get logo image URL
     */
    public function getLogoImageUrl()
    {
        if ($this->logo_image) {
            return asset('storage/' . $this->logo_image);
        }
        return null;
    }

    /**
     * Get floating image URL
     */
    public function getFloatingImageUrl()
    {
        if ($this->floating_image) {
            return asset('storage/' . $this->floating_image);
        }
        return null;
    }

    /**
     * Get medal image URL
     */
    public function getMedalImageUrl()
    {
        if ($this->medal_image) {
            return asset('storage/' . $this->medal_image);
        }
        return null;
    }

    /**
     * Get jersey image URL
     */
    public function getJerseyImageUrl()
    {
        if ($this->jersey_image) {
            return asset('storage/' . $this->jersey_image);
        }
        return null;
    }

    /**
     * Check if registration is open
     */
    public function isRegistrationOpen(): bool
    {
        $now = now();
        
        if ($this->registration_open_at && $now < $this->registration_open_at) {
            return false;
        }
        
        if ($this->registration_close_at && $now > $this->registration_close_at) {
            return false;
        }
        
        return true;
    }
}