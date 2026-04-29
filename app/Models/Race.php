<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Race extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'slug',
        'logo_path',
        'created_by',
        'is_published',
        'published_at',
        'description',
        'location_name',
        'start_at',
        'end_at',
        'banner_path',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
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

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
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
