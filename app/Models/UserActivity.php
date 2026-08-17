<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    protected $fillable = [
        'user_id',
        'master_gpx_id',
        'title',
        'sport_type',
        'start_time',
        'end_time',
        'distance_km',
        'moving_time_s',
        'elapsed_time_s',
        'avg_pace_sec',
        'max_pace_sec',
        'avg_speed_kmh',
        'elevation_gain_m',
        'elevation_loss_m',
        'calories',
        'coordinates_json',
        'splits_json',
        'notes',
        'is_public',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'distance_km' => 'float',
        'moving_time_s' => 'integer',
        'elapsed_time_s' => 'integer',
        'avg_pace_sec' => 'integer',
        'max_pace_sec' => 'integer',
        'avg_speed_kmh' => 'float',
        'elevation_gain_m' => 'float',
        'elevation_loss_m' => 'float',
        'calories' => 'integer',
        'coordinates_json' => 'array',
        'splits_json' => 'array',
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function masterGpx(): BelongsTo
    {
        return $this->belongsTo(MasterGpx::class, 'master_gpx_id');
    }

    /**
     * Format average pace (e.g. 330 seconds -> "5:30 /km")
     */
    public function getFormattedAvgPaceAttribute(): string
    {
        if (!$this->avg_pace_sec || $this->avg_pace_sec <= 0) {
            return '--:-- /km';
        }
        $m = floor($this->avg_pace_sec / 60);
        $s = $this->avg_pace_sec % 60;
        return sprintf('%d:%02d /km', $m, $s);
    }

    /**
     * Format moving duration (e.g. 3665 seconds -> "1:01:05" or "45:20")
     */
    public function getFormattedMovingTimeAttribute(): string
    {
        $total = $this->moving_time_s ?: $this->elapsed_time_s ?: 0;
        $h = floor($total / 3600);
        $m = floor(($total % 3600) / 60);
        $s = $total % 60;

        if ($h > 0) {
            return sprintf('%d:%02d:%02d', $h, $m, $s);
        }
        return sprintf('%02d:%02d', $m, $s);
    }
}
