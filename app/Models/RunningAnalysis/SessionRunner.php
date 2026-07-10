<?php

namespace App\Models\RunningAnalysis;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SessionRunner extends Pivot
{
    protected $table = 'running_analysis_session_runner';

    public $incrementing = true;

    protected $fillable = [
        'session_id',
        'runner_id',
        'sequence_no',
        'status',
        'notes',
        'consent_pose',
        'consent_video',
        'consent_report',
        'consent_ai',
    ];

    protected function casts(): array
    {
        return [
            'sequence_no'    => 'integer',
            'consent_pose'   => 'boolean',
            'consent_video'  => 'boolean',
            'consent_report' => 'boolean',
            'consent_ai'     => 'boolean',
        ];
    }

    // ------------------------------------------------------------------
    // Status constants
    // ------------------------------------------------------------------

    public const STATUS_PENDING          = 'pending';
    public const STATUS_CAPTURED         = 'captured';
    public const STATUS_ANALYZED         = 'analyzed';
    public const STATUS_PUBLISHED        = 'published';
    public const STATUS_REPEAT_REQUIRED  = 'repeat_required';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CAPTURED,
        self::STATUS_ANALYZED,
        self::STATUS_PUBLISHED,
        self::STATUS_REPEAT_REQUIRED,
    ];

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
}
