<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Participant extends Model
{
    protected $fillable = [
        'transaction_id',
        'event_package_id',
        'race_category_id',
        'name',
        'gender',
        'phone',
        'email',
        'id_card',
        'emergency_contact_name',
        'emergency_contact_number',
        'date_of_birth',
        'target_time',
        'bib_number',
        'jersey_size',
        'addons',
        'status',
        'is_picked_up',
        'picked_up_at',
        'picked_up_by',
        'price_type',
    ];

    protected $casts = [
        'is_picked_up' => 'boolean',
        'picked_up_at' => 'datetime',
        'addons' => 'array',
        'date_of_birth' => 'date',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(EventPackage::class, 'event_package_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RaceCategory::class, 'race_category_id');
    }

    /**
     * Calculate age group based on event start date
     */
    public function getAgeGroup($eventDate)
    {
        if (! $this->date_of_birth || ! $eventDate) {
            return '-';
        }

        $eventDate = \Carbon\Carbon::parse($eventDate);
        $age = $this->date_of_birth->diffInYears($eventDate);
/*
        if ($age >= 50) {
            return '50+';
        }*/
        if ($age >= 45) {
            return 'Master 45+';
        }
        /*if ($age >= 40) {
            return 'Master';
        }*/

        return 'Umum';
    }
}
