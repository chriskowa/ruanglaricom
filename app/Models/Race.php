<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Race extends Model
{
    protected $fillable = [
        'name',
        'logo_path',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(RaceSession::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(RaceSessionParticipant::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(RaceCertificate::class);
    }
}

