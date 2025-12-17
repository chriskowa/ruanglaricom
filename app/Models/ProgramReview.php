<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramReview extends Model
{
    protected $fillable = [
        'program_id',
        'runner_id',
        'rating',
        'review',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'runner_id');
    }

    /**
     * Scope for approved reviews
     */
    public function scopeApproved($query)
    {
        // For now, all reviews are approved. Can add moderation later
        return $query;
    }

    /**
     * Scope for specific rating
     */
    public function scopeRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }
}
