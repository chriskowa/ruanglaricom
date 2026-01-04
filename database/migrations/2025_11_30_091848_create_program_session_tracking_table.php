<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('program_session_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('program_enrollments')->onDelete('cascade');
            $table->integer('session_day'); // Day number in program
            $table->enum('status', ['pending', 'started', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            // Ensure one tracking record per enrollment per session day
            $table->unique(['enrollment_id', 'session_day']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_session_tracking');
    }
};
