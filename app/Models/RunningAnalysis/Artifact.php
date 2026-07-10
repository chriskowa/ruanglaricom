<?php

namespace App\Models\RunningAnalysis;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Artifact extends Model
{
    protected $table = 'running_analysis_artifacts';

    public $timestamps = false;

    protected $fillable = [
        'trial_id',
        'type',
        'disk',
        'path',
        'mime_type',
        'compression',
        'sha256',
        'size_bytes',
        'metadata_json',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes'    => 'integer',
            'metadata_json' => 'array',
            'created_at'    => 'datetime',
        ];
    }

    // ------------------------------------------------------------------
    // Type constants
    // ------------------------------------------------------------------

    public const TYPE_POSE_LANDMARKS     = 'pose_landmarks';
    public const TYPE_SMOOTHED_LANDMARKS = 'smoothed_landmarks';
    public const TYPE_VIDEO_CLIP         = 'video_clip';
    public const TYPE_PREVIEW_IMAGE      = 'preview_image';
    public const TYPE_DEBUG              = 'debug';

    public const TYPES = [
        self::TYPE_POSE_LANDMARKS,
        self::TYPE_SMOOTHED_LANDMARKS,
        self::TYPE_VIDEO_CLIP,
        self::TYPE_PREVIEW_IMAGE,
        self::TYPE_DEBUG,
    ];

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class, 'trial_id');
    }
}
