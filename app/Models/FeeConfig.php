<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeConfig extends Model
{
    protected $fillable = [
        'module',
        'fee_percentage',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fee_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
