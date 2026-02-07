<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityParticipant extends Model
{
    protected $fillable = [
        'community_registration_id',
        'community_member_id',
        'event_id',
        'race_category_id',
        'name',
        'gender',
        'email',
        'phone',
        'id_card',
        'date_of_birth',
        'jersey_size',
        'address',
        'emergency_contact_name',
        'emergency_contact_number',
        'base_price',
        'is_free',
        'final_price',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'base_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'is_free' => 'boolean',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(CommunityRegistration::class, 'community_registration_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RaceCategory::class, 'race_category_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(CommunityMember::class, 'community_member_id');
    }
}
