<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_analysis_trials', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->foreignId('session_id')->constrained('running_analysis_sessions')->cascadeOnDelete();
            $table->foreignId('runner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('operator_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_no')->default(1);
            $table->string('direction', 20)->default('unknown');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('camera_device_label')->nullable();
            $table->unsignedSmallInteger('camera_width')->nullable();
            $table->unsignedSmallInteger('camera_height')->nullable();
            $table->decimal('camera_fps', 5, 2)->nullable();
            $table->decimal('inference_fps', 5, 2)->nullable();
            $table->string('pose_model', 100)->default('pose_landmarker');
            $table->string('pose_model_version', 50)->nullable();
            $table->string('capture_version', 20)->default('1.0');
            $table->string('analysis_version', 20)->nullable();
            $table->string('ruleset_version', 20)->nullable();
            $table->string('status', 30)->default('created');
            $table->string('quality_grade', 10)->nullable();
            $table->decimal('quality_score', 5, 4)->nullable();
            $table->string('invalid_reason')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index('session_id');
            $table->index('runner_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_analysis_trials');
    }
};
