<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('race_session_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('race_id')->constrained('races')->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->string('bib_number', 32);
            $table->string('name', 255);
            $table->unsignedInteger('predicted_time_ms')->nullable();
            $table->timestamps();

            $table->unique(['race_id', 'bib_number']);
            $table->index(['race_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('race_session_participants');
    }
};

