<?php

namespace App\Models\RunningAnalysis;

use App\Models\RunningAnalysis\Session;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisRequest extends Model
{
    use HasFactory;

    protected $table = 'running_analysis_requests';

    protected $fillable = [
        'runner_id',
        'runner_name',
        'runner_email',
        'focus_area',
        'goals',
        'notes',
        'video_url',
        'preferred_location',
        'preferred_date',
        'status',
        'admin_notes',
        'handled_by',
        'session_id',
        'handled_at',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'handled_at'     => 'datetime',
        ];
    }

    public function hasVideo(): bool
    {
        return ! empty($this->video_url);
    }

    // ------------------------------------------------------------------
    // Status constants
    // ------------------------------------------------------------------

    public const STATUS_PENDING   = 'pending';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED  = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_SCHEDULED,
        self::STATUS_COMPLETED,
        self::STATUS_REJECTED,
    ];

    public const FOCUS_AREAS = [
        'form'       => 'Form Lari (Running Form)',
        'gait'       => 'Gait & Footstrike',
        'injury'     => 'Cegah Cedera',
        'performance'=> 'Peningkatan Performa',
        'general'    => 'Analisis Umum',
    ];

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'runner_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function focusAreaLabel(): string
    {
        return self::FOCUS_AREAS[$this->focus_area] ?? $this->focus_area;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING   => 'Menunggu',
            self::STATUS_APPROVED  => 'Disetujui',
            self::STATUS_SCHEDULED => 'Dijadwalkan',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_REJECTED  => 'Ditolak',
            default                => ucfirst($this->status),
        };
    }
}




