<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventSubmissionOtp extends Model
{
    protected $table = 'event_submission_otps';

    protected $fillable = [
        'id',
        'email',
        'code_hash',
        'expires_at',
        'attempts',
        'max_attempts',
        'used_at',
        'ip_hash',
        'ua_hash',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'attempts' => 'integer',
        'max_attempts' => 'integer',
    ];

    public $incrementing = false;

    protected $keyType = 'string';
}

