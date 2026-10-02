<?php

return [
    'trial_expire_minutes' => (int) env('KP_TRIAL_EXPIRE_MIN', 60 * 24 * 7),
    'temp_asset_expire_hours' => (int) env('KP_TEMP_ASSET_EXPIRE_HOURS', 24),
    'signed_pdf_expire_hours' => (int) env('KP_PDF_EXPIRE_HOURS', 24),
    'anon_trial_photo_max_files' => (int) env('KP_ANON_PHOTO_MAX_FILES', 8),
    'anon_trial_photo_max_total_mb' => (int) env('KP_ANON_PHOTO_MAX_TOTAL_MB', 20),
    'autosave_debounce_ms' => (int) env('KP_AUTOSAVE_MS', 2000),
    'free_project_max' => (int) env('KP_FREE_PROJECT_MAX', 3),
];
