<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceResult extends Model
{
    protected $fillable = [
        'event_id',
        'race_category_id',
        'bib_number',
        'runner_name',
        'gender',
        'nationality',
        'category_code',
        'gun_time',
        'chip_time',
        'pace',
        'rank_overall',
        'rank_category',
        'rank_gender',
        'is_podium',
        'podium_position',
        'notes',
    ];

    protected $casts = [
        'is_podium' => 'boolean',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RaceCategory::class, 'race_category_id');
    }

    /**
     * Format chip time untuk display
     */
    public function getFormattedChipTime(): string
    {
        if (!$this->chip_time) {
            return '--:--:--';
        }
        
        // Time field dari database sudah dalam format string H:i:s
        return $this->chip_time;
    }

    /**
     * Format gun time untuk display
     */
    public function getFormattedGunTime(): string
    {
        if (!$this->gun_time) {
            return '--:--:--';
        }
        
        // Time field dari database sudah dalam format string H:i:s
        return $this->gun_time;
    }

    /**
     * Scope untuk filter per event
     */
    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope untuk filter per kategori
     */
    public function scopeForCategory($query, $categoryCode)
    {
        return $query->where('category_code', $categoryCode);
    }

    /**
     * Scope untuk filter per gender
     */
    public function scopeForGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope untuk juara (podium)
     */
    public function scopePodium($query)
    {
        return $query->where('is_podium', true);
    }
}



