<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    protected $fillable = [
        'province_id',
        'name',
        'seourl',
        'latitude',
        'longitude',
    ];

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function runningEvents(): HasMany
    {
        return $this->hasMany(RunningEvent::class);
    }
}
