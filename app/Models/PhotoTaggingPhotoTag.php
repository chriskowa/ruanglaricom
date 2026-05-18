<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoTaggingPhotoTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'photo_tagging_photo_id',
        'bib_number',
        'source',
        'confidence',
    ];

    /**
     * Get the event that owns the tag.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    /**
     * Get the photo that owns the tag.
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(PhotoTaggingPhoto::class, 'photo_tagging_photo_id');
    }
}
