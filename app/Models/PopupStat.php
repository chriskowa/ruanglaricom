<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopupStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'popup_id',
        'stat_date',
        'views',
        'clicks',
        'conversions',
    ];

    protected $casts = [
        'stat_date' => 'date',
    ];

    public function popup()
    {
        return $this->belongsTo(Popup::class);
    }
}
