<?php

namespace App\Models\RunningAnalysis;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    protected $table = 'running_analysis_recommendations';

    protected $fillable = [
        'trial_id',
        'finding_id',
        'recommendation_code',
        'type',
        'title',
        'description',
        'priority',
        'source',
        'catalog_version',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
        ];
    }

    // ------------------------------------------------------------------
    // Constants
    // ------------------------------------------------------------------

    public const TYPE_CUE      = 'cue';
    public const TYPE_DRILL    = 'drill';
    public const TYPE_STRENGTH = 'strength';
    public const TYPE_REFERRAL = 'referral';
    public const TYPE_SETUP    = 'setup';

    public const TYPES = [
        self::TYPE_CUE,
        self::TYPE_DRILL,
        self::TYPE_STRENGTH,
        self::TYPE_REFERRAL,
        self::TYPE_SETUP,
    ];

    public const SOURCE_DETERMINISTIC = 'deterministic';
    public const SOURCE_AI_REWORDED   = 'ai_reworded';
    public const SOURCE_OPERATOR      = 'operator';

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class, 'trial_id');
    }

    public function finding(): BelongsTo
    {
        return $this->belongsTo(Finding::class, 'finding_id');
    }
}
