<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_analysis_recommendations', function (Blueprint $table) {
            $table->id();
            $table->char('trial_id', 36);
            $table->foreignId('finding_id')->nullable()->constrained('running_analysis_findings')->nullOnDelete();
            $table->string('recommendation_code', 80);
            $table->string('type', 15);
            $table->string('title');
            $table->text('description');
            $table->unsignedTinyInteger('priority')->default(0);
            $table->string('source', 20)->default('deterministic');
            $table->string('catalog_version', 20);
            $table->timestamps();

            $table->foreign('trial_id')->references('id')->on('running_analysis_trials')->cascadeOnDelete();
            $table->index('trial_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_analysis_recommendations');
    }
};
