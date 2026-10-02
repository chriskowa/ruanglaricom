<?php

namespace App\Models\KalenderPelari;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarElement extends Model
{
    use HasFactory;

    protected $table = 'kp_calendar_elements';

    protected $fillable = [
        'page_id', 'asset_id', 'element_type',
        'x_mm', 'y_mm', 'width_mm', 'height_mm', 'rotation_deg', 'z_index',
        'locked', 'visible', 'style_config', 'content_json', 'data_binding',
    ];

    protected $casts = [
        'rotation_deg' => 'float',
        'locked' => 'boolean',
        'visible' => 'boolean',
        'style_config' => 'array',
        'data_binding' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(CalendarPage::class, 'page_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(UploadedAsset::class, 'asset_id');
    }

    public function isDraggable(): bool
    {
        return ! $this->locked;
    }
}
