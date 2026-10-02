<?php

namespace App\Models\KalenderPelari;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarPage extends Model
{
    use HasFactory;

    protected $table = 'kp_calendar_pages';

    protected $fillable = [
        'project_id', 'month_number', 'page_type',
        'canvas_width_mm', 'canvas_height_mm', 'layout_config',
    ];

    protected $casts = [
        'layout_config' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(CalendarProject::class, 'project_id');
    }

    public function elements(): HasMany
    {
        return $this->hasMany(CalendarElement::class, 'page_id')
            ->orderBy('z_index')
            ->orderBy('id');
    }

    public function monthName(string $locale = 'id_ID'): string
    {
        $map = [
            0 => 'Cover',
            1 => 'Januari', '2' => 'Februari', '3' => 'Maret', '4' => 'April',
            5 => 'Mei', '6' => 'Juni', '7' => 'Juli', '8' => 'Agustus',
            9 => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
            13 => 'Ringkasan Tahunan',
        ];

        return $map[$this->month_number] ?? "Halaman {$this->month_number}";
    }
}
