<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Models\RaceDistance;

class Event extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'short_description',
        'full_description',
        'terms_and_conditions',
        'start_at',
        'end_at',
        'location_name',
        'location_address',
        'location_lat',
        'location_lng',
        'slug',
        'hardcoded',
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
        'addons',
        'jersey_sizes',
        'gallery',
        'theme_colors',
        'premium_amenities',
        'template',
        'platform_fee',
        'sponsors',
        'external_registration_link',
        'social_media_link',
        'organizer_name',
        'organizer_contact',
        'contributor_contact',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'registration_open_at' => 'datetime',
        'registration_close_at' => 'datetime',
        'facilities' => 'array',
        'addons' => 'array',
        'jersey_sizes' => 'array',
        'gallery' => 'array',
        'theme_colors' => 'array',
        'premium_amenities' => 'array',
        'sponsors' => 'array',
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

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function raceType(): BelongsTo
    {
        return $this->belongsTo(RaceType::class);
    }

    public function raceDistances(): BelongsToMany
    {
        return $this->belongsToMany(RaceDistance::class, 'event_distances');
    }

    public function masterGpxes(): HasMany
    {
        return $this->hasMany(MasterGpx::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_at', '>=', now())->orderBy('start_at', 'asc');
    }

    /**
     * Get hero image URL (prioritize uploaded image over URL)
     */
    public function getHeroImageUrl()
    {
        if (! empty($this->attributes['hero_image'])) {
            return asset('storage/'.$this->attributes['hero_image']);
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
            return asset('storage/'.$this->logo_image);
        }

        return null;
    }

    /**
     * Get floating image URL
     */
    public function getFloatingImageUrl()
    {
        if ($this->floating_image) {
            return asset('storage/'.$this->floating_image);
        }

        return null;
    }

    /**
     * Get medal image URL
     */
    public function getMedalImageUrl()
    {
        if ($this->medal_image) {
            return asset('storage/'.$this->medal_image);
        }

        return null;
    }

    /**
     * Get jersey image URL
     */
    public function getJerseyImageUrl()
    {
        if ($this->jersey_image) {
            return asset('storage/'.$this->jersey_image);
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

    /**
     * Accessor for backward compatibility with RunningEvent
     */
    public function getEventDateAttribute()
    {
        return $this->start_at;
    }

    public function getStartTimeAttribute()
    {
        return $this->start_at;
    }

    public function getDistancesAttribute()
    {
        // Combine raceDistances and categories
        $distances = collect();

        if ($this->relationLoaded('raceDistances')) {
            $distances = $distances->concat($this->getRelation('raceDistances'));
        }

        if ($this->relationLoaded('categories')) {
            $mappedCategories = $this->getRelation('categories')->map(function ($cat) {
                return new RaceDistance([
                    'name' => $cat->name,
                    'distance_meter' => ($cat->distance_km ?? 0) * 1000,
                ]);
            });
            $distances = $distances->concat($mappedCategories);
        }

        return $distances->unique('name');
    }

    public function getIsEoAttribute()
    {
        // If user_id is not 1 (Admin), it's likely an EO event
        // Or check if it has internal categories
        return $this->user_id !== 1;
    }

    public function getPublicUrlAttribute()
    {
        // Jika ada external link, berarti ini event aggregator (ex running_events) -> /event-lari/
        // Atau jika user_id == 1 (Admin) -> /event-lari/
        // Jika event EO (internal registration) -> /events/
        
        if ($this->external_registration_link || $this->user_id === 1 || $this->user_id === null) {
            return route('running-event.detail', $this->slug);
        }

        return route('events.show', $this->slug);
    }
}
