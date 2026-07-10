<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_analysis_ai_runs', function (Blueprint $table) {
            $table->id();
            $table->char('trial_id', 36);
            $table->foreignId('report_id')->nullable()->constrained('running_analysis_reports')->nullOnDelete();
            $table->string('provider', 30)->default('openai');
            $table->string('model', 80);
            $table->string('prompt_version', 20);
            $table->string('schema_version', 20);
            $table->char('input_hash', 64);
            $table->string('input_payload_path', 500)->nullable();
            $table->string('response_id', 100)->nullable();
            $table->json('raw_output_json')->nullable();
            $table->json('parsed_output_json')->nullable();
            $table->string('status', 15)->default('queued');
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->string('review_action', 30)->nullable();
            $table->timestamps();

            $table->foreign('trial_id')->references('id')->on('running_analysis_trials')->cascadeOnDelete();
            $table->index('trial_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_analysis_ai_runs');
    }
};
