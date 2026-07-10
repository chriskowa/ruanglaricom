<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_analysis_metrics', function (Blueprint $table) {
            $table->id();
            $table->char('trial_id', 36);
            $table->unsignedSmallInteger('stride_index')->nullable();
            $table->string('side', 10)->nullable();
            $table->string('metric_code', 80);
            $table->string('category', 15);
            $table->decimal('value_decimal', 10, 4)->nullable();
            $table->json('value_json')->nullable();
            $table->string('unit', 30);
            $table->decimal('confidence', 4, 3)->default(0);
            $table->json('source_frame_indexes_json')->nullable();
            $table->string('calculation_version', 20);
            $table->timestamps();

            $table->foreign('trial_id')->references('id')->on('running_analysis_trials')->cascadeOnDelete();
            $table->index('trial_id');
            $table->index('metric_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_analysis_metrics');
    }
};
