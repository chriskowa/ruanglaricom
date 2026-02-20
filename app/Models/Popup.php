<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Popup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'status',
        'starts_at',
        'ends_at',
        'timezone',
        'content',
        'settings',
        'rules',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'content' => 'array',
        'settings' => 'array',
        'rules' => 'array',
    ];

    public function versions()
    {
        return $this->hasMany(PopupVersion::class);
    }

    public function stats()
    {
        return $this->hasMany(PopupStat::class);
    }
}
