<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'coach_id',
        'title',
        'slug',
        'description',
        'difficulty',
        'distance_target',
        'target_time',
        'price',
        'city_id',
        'program_json',
        'is_vdot_generated',
        'vdot_score',
        'is_active',
        'thumbnail',
        'banner',
        'is_published',
        'is_challenge',
        'duration_weeks',
        'enrolled_count',
        'average_rating',
        'total_reviews',
        'is_self_generated',
        'daniels_params',
        'generated_vdot',
        'hardcoded',
    ];

    protected $appends = [
        'thumbnail_url',
        'banner_url',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'program_json' => 'array',
            'target_time' => 'datetime',
            'price' => 'decimal:2',
            'is_vdot_generated' => 'boolean',
            'vdot_score' => 'decimal:2',
            'is_active' => 'boolean',
            'is_published' => 'boolean',
            'is_challenge' => 'boolean',
            'enrolled_count' => 'integer',
            'average_rating' => 'decimal:2',
            'total_reviews' => 'integer',
            'duration_weeks' => 'integer',
            'is_self_generated' => 'boolean',
            'daniels_params' => 'array',
            'generated_vdot' => 'decimal:2',
        ];
    }

    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_id');
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(ProgramEnrollment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProgramReview::class);
    }

    /**
     * Check if program is free
     */
    public function isFree(): bool
    {
        return $this->price == 0 || $this->price === null;
    }

    /**
     * Check if user can purchase this program
     */
    public function canBePurchasedBy(User $user): bool
    {
        // Check if already enrolled
        if ($this->enrollments()->where('runner_id', $user->id)->exists()) {
            return false;
        }

        // Free programs can always be enrolled
        if ($this->isFree()) {
            return true;
        }

        // Check if program is published
        if (!$this->is_published || !$this->is_active) {
            return false;
        }

        return true;
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->thumbnail) {
            return null;
        }

        return asset('storage/' . ltrim($this->thumbnail, '/'));
    }

    /**
     * Get banner URL
     */
    public function getBannerUrlAttribute(): ?string
    {
        if (!$this->banner) {
            return null;
        }

        return asset('storage/' . ltrim($this->banner, '/'));
    }

    /**
     * Get generic image URL (thumbnail fallback to default)
     */
    public function getImageUrlAttribute(): string
    {
        return $this->thumbnail_url ?? asset('images/product/1.jpg');
    }

    /**
     * Policy untuk authorization
     */
    public function authorize($ability, $arguments = [])
    {
        return app(\Illuminate\Contracts\Auth\Access\Gate::class)->authorize($ability, $this, $arguments);
    }
}
