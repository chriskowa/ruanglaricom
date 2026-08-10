<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterGpx extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'user_id',
        'title',
        'slug',
        'city',
        'description',
        'gpx_path',
        'distance_km',
        'elevation_gain_m',
        'elevation_loss_m',
        'coordinates_json',
        'is_published',
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->title);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->title, $model->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = \Illuminate\Support\Str::slug($title);
        if (empty($slug)) {
            $slug = 'rute-gpx-' . time();
        }

        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:3',
            'is_published' => 'boolean',
            'coordinates_json' => 'array',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
