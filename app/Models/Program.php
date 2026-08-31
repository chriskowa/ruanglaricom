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
        'is_featured',
        'duration_weeks',
        'enrolled_count',
        'average_rating',
        'total_reviews',
        'is_self_generated',
        'daniels_params',
        'generated_vdot',
        'hardcoded',
        'pricing_model',
        'price_hourly',
        'price_daily',
        'price_weekly',
        'price_monthly',
        'session_quota',
        'allow_manual_payment',
    ];

    protected $appends = [
        'thumbnail_url',
        'banner_url',
        'image_url',
        'pricing_model_label',
        'display_price_formatted',
    ];

    protected function casts(): array
    {
        return [
            'program_json' => 'array',
            'target_time' => 'datetime',
            'price' => 'decimal:2',
            'price_hourly' => 'decimal:2',
            'price_daily' => 'decimal:2',
            'price_weekly' => 'decimal:2',
            'price_monthly' => 'decimal:2',
            'session_quota' => 'integer',
            'allow_manual_payment' => 'boolean',
            'is_vdot_generated' => 'boolean',
            'vdot_score' => 'decimal:2',
            'is_active' => 'boolean',
            'is_published' => 'boolean',
            'is_challenge' => 'boolean',
            'is_featured' => 'boolean',
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

    public function invoices(): HasMany
    {
        return $this->hasMany(CoachInvoice::class);
    }

    /**
     * Get human readable pricing model label
     */
    public function getPricingModelLabelAttribute(): string
    {
        return match ($this->pricing_model) {
            'hourly' => 'Per Jam / Sesi',
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'custom_package' => 'Paket Sesi',
            default => 'Sekali Beli (Paket Utuh)',
        };
    }

    /**
     * Get formatted display price with unit
     */
    public function getDisplayPriceFormattedAttribute(): string
    {
        if ($this->isFree()) {
            return 'Gratis';
        }

        return match ($this->pricing_model) {
            'hourly' => 'Rp ' . number_format($this->price_hourly ?: $this->price, 0, ',', '.') . ' / jam',
            'daily' => 'Rp ' . number_format($this->price_daily ?: $this->price, 0, ',', '.') . ' / hari',
            'weekly' => 'Rp ' . number_format($this->price_weekly ?: $this->price, 0, ',', '.') . ' / minggu',
            'monthly' => 'Rp ' . number_format($this->price_monthly ?: $this->price, 0, ',', '.') . ' / bulan',
            default => 'Rp ' . number_format($this->price, 0, ',', '.'),
        };
    }

    /**
     * Check if program is free
     */
    public function isFree(): bool
    {
        $basePrice = match ($this->pricing_model) {
            'hourly' => $this->price_hourly,
            'daily' => $this->price_daily,
            'weekly' => $this->price_weekly,
            'monthly' => $this->price_monthly,
            default => $this->price,
        };

        return ($basePrice == 0 || $basePrice === null) && ($this->price == 0 || $this->price === null);
    }

    /**
     * Check if user can purchase this program
     */
    public function canBePurchasedBy(User $user): bool
    {
        // Check if already enrolled in an active or purchased program
        if ($this->enrollments()->where('runner_id', $user->id)->whereIn('status', ['purchased', 'active'])->exists()) {
            return false;
        }

        // Free programs can always be enrolled
        if ($this->isFree()) {
            return true;
        }

        // Check if program is published
        if (! $this->is_published || ! $this->is_active) {
            return false;
        }

        return true;
    }

    /**
     * Get thumbnail URL
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (! $this->thumbnail) {
            return null;
        }

        return asset('storage/'.ltrim($this->thumbnail, '/'));
    }

    /**
     * Get banner URL
     */
    public function getBannerUrlAttribute(): ?string
    {
        if (! $this->banner) {
            return null;
        }

        return asset('storage/'.ltrim($this->banner, '/'));
    }

    /**
     * Get generic image URL (thumbnail fallback to default)
     */
    public function getImageUrlAttribute(): string
    {
        return $this->thumbnail_url ?? $this->banner_url ?? asset('images/product/program lari ruang lari.webp');
    }

    /**
     * Policy untuk authorization
     */
    public function authorize($ability, $arguments = [])
    {
        return app(\Illuminate\Contracts\Auth\Access\Gate::class)->authorize($ability, $this, $arguments);
    }
}
