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
        'route_type',
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
            $model->extractElevationStats();
            $model->determineRouteType();
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->title, $model->id);
            }
            if ($model->isDirty('coordinates_json')) {
                $model->extractStartCoordinates();
                $model->extractElevationStats();
            }
            if (empty($model->route_type) || ($model->isDirty('title') && empty($model->attributes['route_type']))) {
                $model->determineRouteType();
            }
        });
    }

    public function determineRouteType(): void
    {
        if (! empty($this->route_type) && in_array($this->route_type, ['road', 'trail', 'track'])) {
            return;
        }

        $text = strtolower(($this->title ?? '') . ' ' . ($this->notes ?? '') . ' ' . ($this->description ?? ''));
        $trailKeywords = ['trail', 'gunung', 'bukit', 'summit', 'ridge', 'forest', 'tahura', 'rinjani', 'merbabu', 'bromo', 'sikunir', 'lawu', 'ciremai', 'semeru', 'patuha', 'kawah', 'curug', 'alas'];

        foreach ($trailKeywords as $kw) {
            if (str_contains($text, $kw)) {
                $this->route_type = 'trail';
                return;
            }
        }

        $dist = (float) ($this->distance_km ?? 0);
        $gain = (float) ($this->elevation_gain_m ?? 0);

        if ($dist > 0 && $gain > 0) {
            $gainPerKm = $gain / $dist;
            // Steep gradient (>35m gain per km) indicates trail terrain
            if ($gainPerKm >= 35.0) {
                $this->route_type = 'trail';
                return;
            }
        }

        $this->route_type = 'road';
    }

    public function getRouteTypeLabelAttribute(): string
    {
        return match ($this->route_type) {
            'trail' => 'Trail',
            'track' => 'Track',
            default => 'Road',
        };
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

    public function extractElevationStats(): void
    {
        if (empty($this->attributes['elevation_gain_m']) || empty($this->attributes['elevation_loss_m'])) {
            $stats = $this->calculateElevationStats();
            if (empty($this->attributes['elevation_gain_m']) && isset($stats['gain'])) {
                $this->elevation_gain_m = $stats['gain'];
            }
            if (empty($this->attributes['elevation_loss_m']) && isset($stats['loss'])) {
                $this->elevation_loss_m = $stats['loss'];
            }
        }
    }

    public function calculateElevationStats(): array
    {
        $coords = $this->coordinates_json;
        $gain = 0.0;
        $loss = 0.0;
        $prevEle = null;
        $hasRealEle = false;

        if (! empty($coords) && is_array($coords)) {
            foreach ($coords as $pt) {
                $ele = null;
                if (is_array($pt)) {
                    $ele = $pt['ele'] ?? $pt[2] ?? null;
                } elseif (is_object($pt)) {
                    $ele = $pt->ele ?? null;
                }

                if ($ele !== null && is_numeric($ele)) {
                    $hasRealEle = true;
                    if ($prevEle !== null) {
                        $diff = (float) $ele - (float) $prevEle;
                        if ($diff > 0) $gain += $diff;
                        if ($diff < 0) $loss += abs($diff);
                    }
                    $prevEle = $ele;
                }
            }
        }

        if ($hasRealEle && ($gain > 0 || $loss > 0)) {
            return [
                'gain' => (int) round($gain),
                'loss' => (int) round($loss),
            ];
        }

        // Realistic estimated elevation for routes without embedded altitude data
        $dist = (float) ($this->attributes['distance_km'] ?? $this->distance_km ?? 0);
        if ($dist > 0) {
            $simGain = (int) max(5, round($dist * 6.5));
            $simLoss = (int) max(5, round($dist * 6.5));
            return [
                'gain' => $simGain,
                'loss' => $simLoss,
            ];
        }

        return ['gain' => 0, 'loss' => 0];
    }

    public function getElevationGainMAttribute($value)
    {
        if ($value !== null && (float) $value > 0) {
            return (float) $value;
        }

        return (float) ($this->calculateElevationStats()['gain'] ?? 0);
    }

    public function getElevationLossMAttribute($value)
    {
        if ($value !== null && (float) $value > 0) {
            return (float) $value;
        }

        return (float) ($this->calculateElevationStats()['loss'] ?? 0);
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
