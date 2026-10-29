<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class StravaActivity extends Model
{
    protected $fillable = [
        'user_id',
        'strava_activity_id',
        'name',
        'type',
        'start_date',
        'distance_m',
        'moving_time_s',
        'elapsed_time_s',
        'average_speed',
        'total_elevation_gain',
        'raw',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'distance_m' => 'integer',
            'moving_time_s' => 'integer',
            'elapsed_time_s' => 'integer',
            'average_speed' => 'float',
            'total_elevation_gain' => 'float',
            'raw' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStravaUrlAttribute(): string
    {
        return 'https://www.strava.com/activities/'.$this->strava_activity_id;
    }

    public static function parseStravaStartDate(array|object|string|null $data, ?string $fallbackUtc = null): ?Carbon
    {
        return \App\Services\StravaApiService::parseActivityStartDate($data, $fallbackUtc);
    }

    public function getLocalStartDateAttribute(): ?Carbon
    {
        $raw = is_array($this->raw) ? $this->raw : [];
        $parsed = static::parseStravaStartDate($raw);
        if ($parsed) {
            return $parsed;
        }

        return $this->start_date ? $this->start_date->copy()->setTimezone(config('app.timezone')) : null;
    }
}
