<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RaceSession extends Model
{
    protected $fillable = [
        'race_id',
        'slug',
        'category',
        'distance_km',
        'started_at',
        'ended_at',
        'created_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'distance_km' => 'decimal:3',
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function laps(): HasMany
    {
        return $this->hasMany(RaceSessionLap::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(RaceCertificate::class);
    }
}
