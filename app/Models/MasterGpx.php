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
        'city',
        'gpx_path',
        'distance_km',
        'elevation_gain_m',
        'elevation_loss_m',
        'coordinates_json',
        'is_published',
        'notes',
    ];

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
