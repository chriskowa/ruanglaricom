<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class StravaConfig extends Model
{
    protected $fillable = [
        'client_id',
        'client_secret',
        'access_token',
        'refresh_token',
        'expires_at',
        'club_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
