<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterGpx extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'title',
        'gpx_path',
        'distance_km',
        'elevation_gain_m',
        'elevation_loss_m',
        'is_published',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'distance_km' => 'decimal:3',
            'is_published' => 'boolean',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}

