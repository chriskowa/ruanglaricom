<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_analysis_gait_events', function (Blueprint $table) {
            $table->id();
            $table->char('trial_id', 36);
            $table->unsignedSmallInteger('stride_index');
            $table->string('side', 10)->default('unknown');
            $table->string('event_type', 30);
            $table->decimal('timestamp_ms', 10, 2);
            $table->unsignedInteger('frame_index');
            $table->decimal('confidence', 4, 3)->default(0);
            $table->string('source', 15)->default('automatic');
            $table->timestamps();

            $table->foreign('trial_id')->references('id')->on('running_analysis_trials')->cascadeOnDelete();
            $table->index('trial_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_analysis_gait_events');
    }
};
