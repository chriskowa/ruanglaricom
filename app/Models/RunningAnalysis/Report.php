<?php

namespace App\Models\RunningAnalysis;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    protected $table = 'running_analysis_reports';

    protected $fillable = [
        'trial_id',
        'runner_id',
        'report_version',
        'status',
        'deterministic_summary_json',
        'runner_narrative_json',
        'coach_notes',
        'disclaimer_version',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'report_version'             => 'integer',
            'deterministic_summary_json' => 'array',
            'runner_narrative_json'      => 'array',
            'published_at'               => 'datetime',
        ];
    }

    // ------------------------------------------------------------------
    // Status constants
    // ------------------------------------------------------------------

    public const STATUS_DRAFT      = 'draft';
    public const STATUS_REVIEWED   = 'reviewed';
    public const STATUS_PUBLISHED  = 'published';
    public const STATUS_SUPERSEDED = 'superseded';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_REVIEWED,
        self::STATUS_PUBLISHED,
        self::STATUS_SUPERSEDED,
    ];

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class, 'trial_id');
    }

    public function runner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'runner_id');
    }

    public function aiRuns(): HasMany
    {
        return $this->hasMany(AiRun::class, 'report_id');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }
}
