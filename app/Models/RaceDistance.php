<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RaceDistance extends Model
{
    protected $fillable = ['name', 'slug', 'distance_meter'];

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(RunningEvent::class, 'running_event_distances');
    }
}
