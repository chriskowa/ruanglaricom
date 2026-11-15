<?php

namespace App\Models\KalenderPelari;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AthleteTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kp_athlete_templates';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'bio',
        'cover_image',
        'photos',
        'accent_color',
        'paper_color',
        'default_font',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'photos' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $template) {
            if (empty($template->slug)) {
                $base = Str::slug($template->name);
                $slug = $base;
                $counter = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$counter}";
                    $counter++;
                }
                $template->slug = $slug;
            }
        });
    }

    /**
     * Resolve public URL for cover image
     */
    public function getCoverUrlAttribute(): string
    {
        if (empty($this->cover_image)) {
            return asset('images/kalender-pelari/bg/815920233_18619567672013478_7707120302547332417_n.jpg');
        }

        if (Str::startsWith($this->cover_image, ['http://', 'https://', '/images/'])) {
            return asset(ltrim($this->cover_image, '/'));
        }

        return Storage::disk('public')->url($this->cover_image);
    }

    /**
     * Resolve all photo URLs (13 photos: 0=cover, 1..12=Jan..Dec)
     */
    public function getPhotoUrlsAttribute(): array
    {
        $urls = [];
        $raw = is_array($this->photos) ? $this->photos : [];

        for ($i = 0; $i <= 12; $i++) {
            $path = $raw[$i] ?? null;
            if ($path) {
                if (Str::startsWith($path, ['http://', 'https://', '/images/'])) {
                    $urls[$i] = asset(ltrim($path, '/'));
                } else {
                    $urls[$i] = Storage::disk('public')->url($path);
                }
            } else {
                $urls[$i] = null;
            }
        }

        return $urls;
    }

    /**
     * Count filled photos
     */
    public function getFilledPhotosCountAttribute(): int
    {
        $raw = is_array($this->photos) ? $this->photos : [];
        return count(array_filter($raw));
    }
}

