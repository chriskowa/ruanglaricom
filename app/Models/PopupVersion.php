<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopupVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'popup_id',
        'version',
        'payload',
        'created_by',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function popup()
    {
        return $this->belongsTo(Popup::class);
    }
}
