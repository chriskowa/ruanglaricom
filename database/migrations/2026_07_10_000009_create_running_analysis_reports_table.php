<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_analysis_reports', function (Blueprint $table) {
            $table->id();
            $table->char('trial_id', 36);
            $table->foreignId('runner_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('report_version')->default(1);
            $table->string('status', 15)->default('draft');
            $table->json('deterministic_summary_json')->nullable();
            $table->json('runner_narrative_json')->nullable();
            $table->text('coach_notes')->nullable();
            $table->string('disclaimer_version', 20)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('trial_id')->references('id')->on('running_analysis_trials')->cascadeOnDelete();
            $table->index('trial_id');
            $table->index('runner_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_analysis_reports');
    }
};
