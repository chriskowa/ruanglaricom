<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Race extends Model
{
    protected $fillable = [
        'name',
        'logo_path',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Race $race) {
            if ($race->logo_path) {
                Storage::disk('public')->delete($race->logo_path);
            }

            $paths = $race->certificates()
                ->pluck('pdf_path')
                ->filter(fn ($p) => is_string($p) && trim($p) !== '')
                ->all();

            if (! empty($paths)) {
                Storage::disk('public')->delete($paths);
            }
        });
    }

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
