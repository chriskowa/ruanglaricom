<?php

namespace App\Models\RunningAnalysis;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaitEvent extends Model
{
    protected $table = 'running_analysis_gait_events';

    protected $fillable = [
        'trial_id',
        'stride_index',
        'side',
        'event_type',
        'timestamp_ms',
        'frame_index',
        'confidence',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'stride_index' => 'integer',
            'timestamp_ms' => 'decimal:2',
            'frame_index'  => 'integer',
            'confidence'   => 'decimal:3',
        ];
    }

    // ------------------------------------------------------------------
    // Constants
    // ------------------------------------------------------------------

    public const SIDE_LEFT    = 'left';
    public const SIDE_RIGHT   = 'right';
    public const SIDE_UNKNOWN = 'unknown';

    public const EVENT_INITIAL_CONTACT    = 'initial_contact';
    public const EVENT_MIDSTANCE          = 'midstance';
    public const EVENT_TOE_OFF            = 'toe_off';
    public const EVENT_MAX_SWING_FLEXION  = 'max_swing_flexion';

    public const EVENT_TYPES = [
        self::EVENT_INITIAL_CONTACT,
        self::EVENT_MIDSTANCE,
        self::EVENT_TOE_OFF,
        self::EVENT_MAX_SWING_FLEXION,
    ];

    public const SOURCE_AUTOMATIC = 'automatic';
    public const SOURCE_OPERATOR  = 'operator';

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class, 'trial_id');
    }
}
