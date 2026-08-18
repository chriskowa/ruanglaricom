<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterGpx extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'user_id',
        'title',
        'slug',
        'city',
        'description',
        'gpx_path',
        'distance_km',
        'elevation_gain_m',
        'elevation_loss_m',
        'coordinates_json',
        'start_latitude',
        'start_longitude',
        'is_published',
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->title);
            }
            $model->extractStartCoordinates();
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->title, $model->id);
            }
            if ($model->isDirty('coordinates_json')) {
                $model->extractStartCoordinates();
            }
        });
    }

    public function extractStartCoordinates(): void
    {
        if (! empty($this->coordinates_json) && is_array($this->coordinates_json)) {
            $first = $this->coordinates_json[0] ?? null;
            if (is_array($first)) {
                $lat = $first['lat'] ?? $first[0] ?? null;
                $lng = $first['lng'] ?? $first[1] ?? null;
                if ($lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng)) {
                    $this->start_latitude = round((float) $lat, 7);
                    $this->start_longitude = round((float) $lng, 7);
                }
            }
        }
    }

    public function calculateDistanceTo(?float $userLat, ?float $userLng): ?float
    {
        if ($userLat === null || $userLng === null || $this->start_latitude === null || $this->start_longitude === null) {
            return null;
        }

        $earthRadiusKm = 6371;
        $dLat = deg2rad($this->start_latitude - $userLat);
        $dLng = deg2rad($this->start_longitude - $userLng);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($userLat)) * cos(deg2rad($this->start_latitude)) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 2);
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = \Illuminate\Support\Str::slug($title);
        if (empty($slug)) {
            $slug = 'rute-gpx-' . time();
        }

        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:3',
            'start_latitude' => 'float',
            'start_longitude' => 'float',
            'is_published' => 'boolean',
            'coordinates_json' => 'array',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
