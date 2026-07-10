<?php

namespace App\Models\RunningAnalysis;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Finding extends Model
{
    protected $table = 'running_analysis_findings';

    protected $fillable = [
        'trial_id',
        'finding_code',
        'category',
        'severity',
        'confidence',
        'evidence_json',
        'explanation_key',
        'ruleset_version',
        'review_status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'confidence'    => 'decimal:3',
            'evidence_json' => 'array',
            'reviewed_at'   => 'datetime',
        ];
    }

    // ------------------------------------------------------------------
    // Constants
    // ------------------------------------------------------------------

    public const SEVERITY_MINOR       = 'minor';
    public const SEVERITY_MODERATE    = 'moderate';
    public const SEVERITY_SIGNIFICANT = 'significant';

    public const REVIEW_GENERATED = 'generated';
    public const REVIEW_ACCEPTED  = 'accepted';
    public const REVIEW_EDITED    = 'edited';
    public const REVIEW_REJECTED  = 'rejected';

    // Standard finding codes
    public const CODE_LANDING_AHEAD_OF_PELVIS      = 'LANDING_AHEAD_OF_PELVIS';
    public const CODE_LOW_LANDING_KNEE_FLEXION      = 'LOW_LANDING_KNEE_FLEXION';
    public const CODE_NON_VERTICAL_SHIN_AT_CONTACT  = 'NON_VERTICAL_SHIN_AT_CONTACT';
    public const CODE_EXCESSIVE_TRUNK_LEAN          = 'EXCESSIVE_TRUNK_LEAN';
    public const CODE_LIMITED_TRAILING_LEG           = 'LIMITED_TRAILING_LEG';
    public const CODE_LIMITED_HIP_EXTENSION_PROXY   = 'LIMITED_HIP_EXTENSION_PROXY';
    public const CODE_DELAYED_LEG_RECOVERY          = 'DELAYED_LEG_RECOVERY';
    public const CODE_LOW_SWING_KNEE_FLEXION        = 'LOW_SWING_KNEE_FLEXION';
    public const CODE_EARLY_LONG_LEVER              = 'EARLY_LONG_LEVER';
    public const CODE_LEFT_RIGHT_TIMING_DIFFERENCE  = 'LEFT_RIGHT_TIMING_DIFFERENCE';
    public const CODE_INCONSISTENT_STRIDES          = 'INCONSISTENT_STRIDES';
    public const CODE_LOW_DATA_QUALITY              = 'LOW_DATA_QUALITY';

    public const FINDING_CODES = [
        self::CODE_LANDING_AHEAD_OF_PELVIS,
        self::CODE_LOW_LANDING_KNEE_FLEXION,
        self::CODE_NON_VERTICAL_SHIN_AT_CONTACT,
        self::CODE_EXCESSIVE_TRUNK_LEAN,
        self::CODE_LIMITED_TRAILING_LEG,
        self::CODE_LIMITED_HIP_EXTENSION_PROXY,
        self::CODE_DELAYED_LEG_RECOVERY,
        self::CODE_LOW_SWING_KNEE_FLEXION,
        self::CODE_EARLY_LONG_LEVER,
        self::CODE_LEFT_RIGHT_TIMING_DIFFERENCE,
        self::CODE_INCONSISTENT_STRIDES,
        self::CODE_LOW_DATA_QUALITY,
    ];

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class, 'trial_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class, 'finding_id');
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    public function isAccepted(): bool
    {
        return in_array($this->review_status, [self::REVIEW_ACCEPTED, self::REVIEW_EDITED]);
    }
}
