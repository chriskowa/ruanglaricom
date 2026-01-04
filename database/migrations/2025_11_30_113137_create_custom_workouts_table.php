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
        Schema::create('custom_workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('runner_id')->constrained('users')->onDelete('cascade');
            $table->date('workout_date');
            $table->string('type')->default('run'); // run, interval, tempo, easy_run, yoga, cycling, rest
            $table->decimal('distance', 8, 2)->nullable(); // in km
            $table->string('duration')->nullable(); // format: HH:MM:SS
            $table->text('description')->nullable();
            $table->enum('difficulty', ['easy', 'moderate', 'hard'])->default('moderate');
            $table->enum('status', ['pending', 'started', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['runner_id', 'workout_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_workouts');
    }
};
