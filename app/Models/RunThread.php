<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RunThread extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'creator_id',
        'community_id',
        'title',
        'description',
        'type',
        'run_distance_km',
        'pace_min',
        'pace_max',
        'start_date',
        'start_time',
        'start_location_name',
        'start_latitude',
        'start_longitude',
        'route_url',
        'gpx_file_path',
        'quota',
        'status',
        'visibility',
        'is_beginner_friendly',
        'is_women_friendly',
        'notes',
    ];

    protected $casts = [
        'run_distance_km' => 'float',
        'start_latitude' => 'float',
        'start_longitude' => 'float',
        'quota' => 'integer',
        'is_beginner_friendly' => 'boolean',
        'is_women_friendly' => 'boolean',
        'start_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function community()
    {
        return $this->belongsTo(Community::class, 'community_id');
    }

    public function participants()
    {
        return $this->hasMany(RunThreadParticipant::class, 'run_thread_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'run_thread_participants', 'run_thread_id', 'user_id')
            ->withPivot('status', 'joined_at')
            ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(RunThreadMessage::class, 'run_thread_id');
    }

    public function ratings()
    {
        return $this->hasMany(UserRating::class, 'run_thread_id');
    }

    /**
     * Scope to filter threads within a given radius using Haversine formula.
     */
    public function scopeCloseTo($query, $latitude, $longitude, $radiusInKm)
    {
        $haversine = "(6371 * acos(cos(radians(?)) * cos(radians(start_latitude)) * cos(radians(start_longitude) - radians(?)) + sin(radians(?)) * sin(radians(start_latitude))))";

        return $query->select('*')
            ->selectRaw("{$haversine} AS distance", [$latitude, $longitude, $latitude])
            ->whereRaw("{$haversine} <= ?", [$latitude, $longitude, $latitude, $radiusInKm]);
    }
}
