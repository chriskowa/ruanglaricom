<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('race_session_laps');

        Schema::create('race_session_laps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->foreignId('race_session_id')->constrained('race_sessions')->cascadeOnDelete();
            $table->foreignId('race_session_participant_id')->constrained('race_session_participants')->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->unsignedInteger('lap_number');
            $table->unsignedInteger('lap_time_ms');
            $table->unsignedInteger('total_time_ms');
            $table->integer('delta_ms')->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['race_id', 'participant_id']);
            $table->index(['race_session_id', 'recorded_at']);
            $table->unique(['race_session_id', 'race_session_participant_id', 'lap_number'], 'u_rsl_session_part_lap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_session_laps');
    }
};
