<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Community extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'pic_name',
        'pic_email',
        'pic_phone',
        'city_id',
        'description',
        'logo',
        'owner_user_id',
        'hero_image',
        'theme_color',
        'wa_group_link',
        'instagram_link',
        'tiktok_link',
        'schedules',
        'captains',
        'faqs',
    ];

    protected $casts = [
        'schedules' => 'array',
        'captains' => 'array',
        'faqs' => 'array',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(CommunityRegistration::class);
    }
}
