<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityMember extends Model
{
    protected $fillable = [
        'community_id',
        'name',
        'email',
        'phone',
        'id_card',
        'gender',
        'date_of_birth',
        'blood_type',
        'jersey_size',
        'address',
        'emergency_contact_name',
        'emergency_contact_number',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(CommunityParticipant::class);
    }
}
