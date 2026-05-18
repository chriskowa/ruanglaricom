<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhotoTaggingPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'image_path',
        'original_name',
        'file_size',
        'mime_type',
        'status',
    ];

    /**
     * Get the event that owns the photo.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * Get the tags for the photo.
     */
    public function tags(): HasMany
    {
        return $this->hasMany(PhotoTaggingPhotoTag::class);
    }

    /**
     * Get the public URL for the image.
     */
    public function getImageUrlAttribute(): string
    {
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }
        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}
