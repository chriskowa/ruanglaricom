<?php

namespace App\Models\RunningAnalysis;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    use HasFactory;

    protected $table = 'running_analysis_sessions';

    protected $fillable = [
        'name',
        'location',
        'session_date',
        'created_by',
        'camera_setup_json',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'session_date'     => 'date',
            'camera_setup_json' => 'array',
        ];
    }

    // ------------------------------------------------------------------
    // Status constants
    // ------------------------------------------------------------------

    public const STATUS_DRAFT     = 'draft';
    public const STATUS_ACTIVE    = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED  = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_ARCHIVED,
    ];

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function runners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'running_analysis_session_runner', 'session_id', 'runner_id')
            ->using(SessionRunner::class)
            ->withPivot([
                'id', 'sequence_no', 'status', 'notes',
                'consent_pose', 'consent_video', 'consent_report', 'consent_ai',
            ])
            ->withTimestamps();
    }

    public function sessionRunners(): HasMany
    {
        return $this->hasMany(SessionRunner::class, 'session_id');
    }

    public function trials(): HasMany
    {
        return $this->hasMany(Trial::class, 'session_id');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
