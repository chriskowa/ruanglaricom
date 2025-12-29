<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pacer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'seo_slug', 'nickname', 'category', 'pace', 'image_url',
        'whatsapp', 'verified', 'total_races', 'bio', 'stats', 'tags', 'race_portfolio'
    ];

    protected $casts = [
        'verified' => 'boolean',
        'stats' => 'array',
        'tags' => 'array',
        'race_portfolio' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
