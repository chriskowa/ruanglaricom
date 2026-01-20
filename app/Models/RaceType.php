<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RaceType extends Model
{
    protected $fillable = ['name', 'slug'];

    public function events(): HasMany
    {
        return $this->hasMany(RunningEvent::class);
    }
}
