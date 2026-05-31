<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JerseySize extends Model
{
    protected $fillable = [
        'event_id',
        'xxs', 'xs', 's', 'm', 'l', 'xl', '2xl', '3xl', '4xl', '5xl'
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
