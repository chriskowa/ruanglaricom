<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration for Running Analysis
    |--------------------------------------------------------------------------
    */

    'openai' => [
        'model'   => env('OPENAI_RUNNING_ANALYSIS_MODEL', 'gpt-4o'),
        'enabled' => (bool) env('OPENAI_RUNNING_ANALYSIS_ENABLED', true),
        'store'   => (bool) env('OPENAI_RUNNING_ANALYSIS_STORE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Retention (days)
    |--------------------------------------------------------------------------
    */

    'retention' => [
        'video_days'    => (int) env('RUNNING_ANALYSIS_VIDEO_RETENTION_DAYS', 30),
        'pose_days'     => (int) env('RUNNING_ANALYSIS_POSE_RETENTION_DAYS', 365),
        'ai_input_days' => (int) env('RUNNING_ANALYSIS_AI_INPUT_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Quality Thresholds
    |--------------------------------------------------------------------------
    */

    'quality' => [
        'min_usable_frame_ratio' => 0.7,
        'min_core_confidence'    => 0.5,
        'min_strides'            => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Capture Parameters
    |--------------------------------------------------------------------------
    */

    'capture' => [
        'max_duration_seconds'          => 15,
        'auto_start_consecutive_frames' => 5,
        'pose_lost_timeout_ms'          => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Limits
    |--------------------------------------------------------------------------
    */

    'upload' => [
        'max_artifact_size_mb' => 50,
        'max_video_size_mb'    => 200,
    ],

];
