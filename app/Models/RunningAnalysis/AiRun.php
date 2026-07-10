<?php

namespace App\Models\RunningAnalysis;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRun extends Model
{
    protected $table = 'running_analysis_ai_runs';

    protected $fillable = [
        'trial_id',
        'report_id',
        'provider',
        'model',
        'prompt_version',
        'schema_version',
        'input_hash',
        'input_payload_path',
        'response_id',
        'raw_output_json',
        'parsed_output_json',
        'status',
        'error_code',
        'error_message',
        'latency_ms',
        'input_tokens',
        'output_tokens',
        'review_action',
    ];

    protected function casts(): array
    {
        return [
            'raw_output_json'    => 'array',
            'parsed_output_json' => 'array',
            'latency_ms'         => 'integer',
            'input_tokens'       => 'integer',
            'output_tokens'      => 'integer',
        ];
    }

    // ------------------------------------------------------------------
    // Status constants
    // ------------------------------------------------------------------

    public const STATUS_QUEUED    = 'queued';
    public const STATUS_RUNNING   = 'running';
    public const STATUS_VALID     = 'valid';
    public const STATUS_INVALID   = 'invalid';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_DISCARDED = 'discarded';

    public const STATUSES = [
        self::STATUS_QUEUED,
        self::STATUS_RUNNING,
        self::STATUS_VALID,
        self::STATUS_INVALID,
        self::STATUS_FAILED,
        self::STATUS_DISCARDED,
    ];

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    public function trial(): BelongsTo
    {
        return $this->belongsTo(Trial::class, 'trial_id');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'report_id');
    }
}
