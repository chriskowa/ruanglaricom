<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class RunningEvent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'banner_image',
        'description',
        'event_date',
        'start_time',
        'city_id',
        'location_name',
        'race_type_id',
        'registration_link',
        'social_media_link',
        'organizer_name',
        'organizer_contact',
        'contributor_contact',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'event_date' => 'date',
        'start_time' => 'datetime:H:i',
        'is_featured' => 'boolean',
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
            if ($event->isDirty('name') && ! $event->isDirty('slug')) {
                $event->slug = Str::slug($event->name);
            }
        });
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
        return $this->belongsToMany(RaceDistance::class, 'running_event_distances');
    }

    public function masterGpxes()
    {
        return $this->hasMany(MasterGpx::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString())->orderBy('event_date', 'asc');
    }
}
