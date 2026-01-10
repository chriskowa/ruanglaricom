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
        'target_time',
        'bib_number',
        'jersey_size',
        'addons',
        'status',
        'is_picked_up',
        'picked_up_at',
        'picked_up_by',
    ];

    protected $casts = [
        'is_picked_up' => 'boolean',
        'picked_up_at' => 'datetime',
        'addons' => 'array',
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
}
