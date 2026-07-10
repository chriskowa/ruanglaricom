<?php

namespace App\Models\RunningAnalysis;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Metric extends Model
{
    protected $table = 'running_analysis_metrics';

    protected $fillable = [
        'trial_id',
        'stride_index',
        'side',
        'metric_code',
        'category',
        'value_decimal',
        'value_json',
        'unit',
        'confidence',
        'source_frame_indexes_json',
        'calculation_version',
    ];

    protected function casts(): array
    {
        return [
            'stride_index'              => 'integer',
            'value_decimal'             => 'decimal:4',
            'value_json'                => 'array',
            'confidence'                => 'decimal:3',
            'source_frame_indexes_json' => 'array',
        ];
    }

    // ------------------------------------------------------------------
    // Category constants
    // ------------------------------------------------------------------

    public const CATEGORY_PULL    = 'pull';
    public const CATEGORY_LAND    = 'land';
    public const CATEGORY_PUSH    = 'push';
    public const CATEGORY_LEVER   = 'lever';
    public const CATEGORY_QUALITY = 'quality';
    public const CATEGORY_GENERAL = 'general';

    public const CATEGORIES = [
        self::CATEGORY_PULL,
        self::CATEGORY_LAND,
        self::CATEGORY_PUSH,
        self::CATEGORY_LEVER,
        self::CATEGORY_QUALITY,
        self::CATEGORY_GENERAL,
    ];

    // ------------------------------------------------------------------
    // Standard metric codes
    // ------------------------------------------------------------------

    public const CODE_LAND_ANKLE_PELVIS_OFFSET     = 'LAND_ANKLE_PELVIS_OFFSET';
    public const CODE_LAND_KNEE_FLEXION            = 'LAND_KNEE_FLEXION';
    public const CODE_LAND_SHIN_ANGLE              = 'LAND_SHIN_ANGLE';
    public const CODE_LAND_FOOT_ANGLE              = 'LAND_FOOT_ANGLE';
    public const CODE_GENERAL_TRUNK_LEAN           = 'GENERAL_TRUNK_LEAN';
    public const CODE_PUSH_TRAILING_LEG_ANGLE      = 'PUSH_TRAILING_LEG_ANGLE';
    public const CODE_PUSH_HIP_EXTENSION_PROXY     = 'PUSH_HIP_EXTENSION_PROXY';
    public const CODE_PULL_MAX_SWING_KNEE_FLEXION  = 'PULL_MAX_SWING_KNEE_FLEXION';
    public const CODE_PULL_HEEL_HIP_DISTANCE       = 'PULL_HEEL_HIP_DISTANCE';
    public const CODE_LEVER_RECOVERY_TIME          = 'LEVER_RECOVERY_TIME';
    public const CODE_LEVER_EARLY_OPENING          = 'LEVER_EARLY_OPENING';
    public const CODE_QUALITY_USABLE_FRAME_RATIO   = 'QUALITY_USABLE_FRAME_RATIO';

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class, 'trial_id');
    }
}
