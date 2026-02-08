<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('race_certificates');

        Schema::create('race_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->foreignId('race_session_id')->constrained('race_sessions')->cascadeOnDelete();
            $table->foreignId('race_session_participant_id')->constrained('race_session_participants')->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->unsignedInteger('final_position')->nullable();
            $table->unsignedInteger('total_time_ms')->nullable();
            $table->string('pdf_path');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['race_session_id', 'race_session_participant_id'], 'u_rc_session_participant');
            $table->index(['race_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_certificates');
    }
};
