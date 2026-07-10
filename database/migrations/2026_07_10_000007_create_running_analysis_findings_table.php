<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_analysis_findings', function (Blueprint $table) {
            $table->id();
            $table->char('trial_id', 36);
            $table->string('finding_code', 80);
            $table->string('category', 15);
            $table->string('severity', 15)->default('moderate');
            $table->decimal('confidence', 4, 3)->default(0);
            $table->json('evidence_json');
            $table->string('explanation_key', 100)->nullable();
            $table->string('ruleset_version', 20);
            $table->string('review_status', 15)->default('generated');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('trial_id')->references('id')->on('running_analysis_trials')->cascadeOnDelete();
            $table->index('trial_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_analysis_findings');
    }
};
