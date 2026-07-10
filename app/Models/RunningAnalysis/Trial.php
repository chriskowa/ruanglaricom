<?php

namespace App\Models\RunningAnalysis;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trial extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'running_analysis_trials';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'session_id',
        'runner_id',
        'operator_id',
        'attempt_no',
        'direction',
        'started_at',
        'ended_at',
        'camera_device_label',
        'camera_width',
        'camera_height',
        'camera_fps',
        'camera_height',
        'inference_fps',
        'pose_model',
        'pose_model_version',
        'capture_version',
        'analysis_version',
        'ruleset_version',
        'status',
        'quality_grade',
        'quality_score',
        'invalid_reason',
        'published_at',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'attempt_no'    => 'integer',
            'camera_width'  => 'integer',
            'camera_height' => 'integer',
            'camera_fps'    => 'decimal:2',
            'inference_fps' => 'decimal:2',
            'quality_score' => 'decimal:4',
            'started_at'    => 'datetime',
            'ended_at'      => 'datetime',
            'published_at'  => 'datetime',
            'approved_at'   => 'datetime',
        ];
    }

    // ------------------------------------------------------------------
    // Status constants
    // ------------------------------------------------------------------

    public const STATUS_CREATED          = 'created';
    public const STATUS_CAPTURING        = 'capturing';
    public const STATUS_UPLOADED         = 'uploaded';
    public const STATUS_QUEUED           = 'queued';
    public const STATUS_ANALYZING        = 'analyzing';
    public const STATUS_REVIEW_REQUIRED  = 'review_required';
    public const STATUS_APPROVED         = 'approved';
    public const STATUS_PUBLISHED        = 'published';
    public const STATUS_INVALID          = 'invalid';
    public const STATUS_FAILED           = 'failed';
    public const STATUS_INTERRUPTED      = 'interrupted';

    public const STATUSES = [
        self::STATUS_CREATED,
        self::STATUS_CAPTURING,
        self::STATUS_UPLOADED,
        self::STATUS_QUEUED,
        self::STATUS_ANALYZING,
        self::STATUS_REVIEW_REQUIRED,
        self::STATUS_APPROVED,
        self::STATUS_PUBLISHED,
        self::STATUS_INVALID,
        self::STATUS_FAILED,
        self::STATUS_INTERRUPTED,
    ];

    // Direction constants
    public const DIRECTION_LEFT_TO_RIGHT = 'left_to_right';
    public const DIRECTION_RIGHT_TO_LEFT = 'right_to_left';
    public const DIRECTION_UNKNOWN       = 'unknown';

    // Quality grade constants
    public const QUALITY_GOOD    = 'good';
    public const QUALITY_USABLE  = 'usable';
    public const QUALITY_POOR    = 'poor';
    public const QUALITY_INVALID = 'invalid';

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class, 'session_id');
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'runner_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function artifacts(): HasMany
    {
        return $this->hasMany(Artifact::class, 'trial_id');
    }

    public function gaitEvents(): HasMany
    {
        return $this->hasMany(GaitEvent::class, 'trial_id');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(Metric::class, 'trial_id');
    }

    public function findings(): HasMany
    {
        return $this->hasMany(Finding::class, 'trial_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class, 'trial_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'trial_id');
    }

    public function latestReport(): HasOne
    {
        return $this->hasOne(Report::class, 'trial_id')->latestOfMany();
    }

    public function aiRuns(): HasMany
    {
        return $this->hasMany(AiRun::class, 'trial_id');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isInvalid(): bool
    {
        return $this->status === self::STATUS_INVALID;
    }

    public function isReviewRequired(): bool
    {
        return $this->status === self::STATUS_REVIEW_REQUIRED;
    }

    public function qualityAllowsAnalysis(): bool
    {
        return in_array($this->quality_grade, [self::QUALITY_GOOD, self::QUALITY_USABLE, self::QUALITY_POOR]);
    }
}
