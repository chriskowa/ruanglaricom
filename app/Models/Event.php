<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'short_description',
        'full_description',
        'terms_and_conditions',
        'start_at',
        'end_at',
        'city_id',      // Added
        'race_type_id', // Added
        'location_name',
        'location_address',
        'location_lat',
        'location_lng',
        'rpc_location_name',
        'rpc_location_address',
        'rpc_latitude',
        'rpc_longitude',
        'slug',
        'hardcoded',
        'hero_image_url',
        'hero_image',
        'logo_image',
        'floating_image',
        'medal_image',
        'jersey_image',
        'twibbon_image',
        'map_embed_url',
        'google_calendar_url',
        'registration_open_at',
        'registration_close_at',
        'promo_code',
        'promo_buy_x',
        'custom_email_message',
        'ticket_email_use_qr',
        'is_instant_notification',
        'ticket_email_rate_limit_per_minute',
        'blast_email_rate_limit_per_minute',
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
        'is_featured',
        'show_participant_list',
        'event_kind', // Added this field
        'status',
        'is_active',
        'lock_version',
        'payment_config',
        'whatsapp_config',
        'sheets_config',
    ];

    protected $casts = [
        'user_id' => 'integer',
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
        'is_instant_notification' => 'boolean',
        'ticket_email_rate_limit_per_minute' => 'integer',
        'blast_email_rate_limit_per_minute' => 'integer',
        'ticket_email_use_qr' => 'boolean',
        'is_featured' => 'boolean',
        'show_participant_list' => 'boolean',
        'is_active' => 'boolean',
        'lock_version' => 'integer',
        'payment_config' => 'array',
        'whatsapp_config' => 'array',
        'sheets_config' => 'array',
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

    public function audits(): HasMany
    {
        return $this->hasMany(EventAudit::class);
    }

    public function participants(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Participant::class, Transaction::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_at', '>=', now())->orderBy('start_at', 'asc');
    }

    public function scopeDirectory($query)
    {
        return $query->where('event_kind', 'directory');
    }

    public function scopeManaged($query)
    {
        return $query->where('event_kind', 'managed');
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
        // 1. External Registration Link -> Listing View (/event-lari/)
        if ($this->external_registration_link) {
            return route('running-event.detail', $this->slug);
        }

        // 2. Check event_kind explicitly
        if ($this->event_kind === 'directory') {
            return route('running-event.detail', $this->slug);
        }

        if ($this->event_kind === 'managed') {
            return route('events.show', $this->slug);
        }

        // 3. Admin / Aggregator Event -> Listing View (/event-lari/)
        // Prioritize Admin events to always use Listing View
        if ($this->user_id === 1) {
            return route('running-event.detail', $this->slug);
        }

        // 3. Internal Registration (Managed Event) -> Landing View (/events/)
        // Event yang dimanage penuh pasti punya jadwal registrasi
        if ($this->registration_open_at) {
            return route('events.show', $this->slug);
        }

        // 4. Event oleh EO (Non-Admin) -> Landing View (/events/)
        // Asumsi EO selalu membuat event managed, meskipun belum set tanggal registrasi
        if ($this->user_id && $this->user_id !== 1) {
            return route('events.show', $this->slug);
        }

        // 5. Fallback (Admin/Aggregator/Listing) -> Listing View (/event-lari/)
        return route('running-event.detail', $this->slug);
    }

    public function photoTaggingPhotos(): HasMany
    {
        return $this->hasMany(PhotoTaggingPhoto::class, 'event_id');
    }

    public function photoTaggingPhotoTags(): HasMany
    {
        return $this->hasMany(PhotoTaggingPhotoTag::class, 'event_id');
    }

    public function getSanitizedDescriptionHtmlAttribute()
    {
        $html = $this->full_description;
        if (empty($html)) {
            return '';
        }

        $dom = new \DOMDocument();
        // Disable error reporting for malformed HTML
        libxml_use_internal_errors(true);
        
        // Load HTML with UTF-8 encoding wrapped in a single root element
        $dom->loadHTML('<?xml encoding="utf-8" ?><div>' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $allowedTags = ['p', 'a', 'b', 'i', 'strong', 'em', 'ul', 'ol', 'li', 'br', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span', 'div', 'img', 'table', 'thead', 'tbody', 'tr', 'th', 'td'];

        // Recursively clean DOM elements
        $cleanNode = function(\DOMNode $node) use (&$cleanNode, $allowedTags) {
            if ($node->nodeType === XML_ELEMENT_NODE) {
                $tagName = strtolower($node->tagName);
                
                // Allow our container div wrapper
                if ($tagName !== 'div' || $node->parentNode->nodeName !== '#document') {
                    if (!in_array($tagName, $allowedTags)) {
                        // Dangerous/unwanted tags are removed entirely or replaced by their text content
                        if (in_array($tagName, ['script', 'iframe', 'style', 'object', 'embed', 'applet', 'meta', 'link'])) {
                            $node->parentNode->removeChild($node);
                            return;
                        } else {
                            // Replace node with text content
                            $textNode = $node->ownerDocument->createTextNode($node->nodeValue);
                            $node->parentNode->replaceChild($textNode, $node);
                            return;
                        }
                    }
                }

                // Clean attributes
                $attributes = [];
                foreach ($node->attributes as $attr) {
                    $attributes[] = $attr->name;
                }

                foreach ($attributes as $attrName) {
                    $attrNameLower = strtolower($attrName);
                    
                    // Strip on* events (onclick, onload, etc.)
                    if (str_starts_with($attrNameLower, 'on')) {
                        $node->removeAttribute($attrName);
                        continue;
                    }

                    // Strip href or src if they start with javascript: or data: (except data:image)
                    if (in_array($attrNameLower, ['href', 'src'])) {
                        $val = strtolower(trim($node->getAttribute($attrName)));
                        if (str_starts_with($val, 'javascript:') || (str_starts_with($val, 'data:') && !str_starts_with($val, 'data:image'))) {
                            $node->removeAttribute($attrName);
                        }
                    }
                }
            }

            // Clean children recursively (copying nodes to a list since childNodes array changes dynamically)
            if ($node->hasChildNodes()) {
                $children = [];
                foreach ($node->childNodes as $child) {
                    $children[] = $child;
                }
                foreach ($children as $child) {
                    if ($child->parentNode) {
                        $cleanNode($child);
                    }
                }
            }
        };

        // Clean container div and its children
        if ($dom->documentElement) {
            $cleanNode($dom->documentElement);
            
            // Extract inner HTML of container div
            $innerHtml = '';
            foreach ($dom->documentElement->childNodes as $child) {
                $innerHtml .= $dom->saveHTML($child);
            }
            return $innerHtml;
        }

        return '';
    }
}
