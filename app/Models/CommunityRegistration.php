<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'community_id',
        'community_name',
        'pic_name',
        'pic_email',
        'pic_phone',
        'status',
        'invoiced_at',
        'paid_at',
        'imported_at',
    ];

    protected $casts = [
        'invoiced_at' => 'datetime',
        'paid_at' => 'datetime',
        'imported_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CommunityParticipant::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(CommunityInvoice::class);
    }
}
