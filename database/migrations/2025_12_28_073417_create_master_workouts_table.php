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
        Schema::create('master_workouts', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // easy_run, long_run, tempo, interval, strength, rest
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('default_distance', 8, 2)->nullable();
            $table->string('default_duration')->nullable();
            $table->string('intensity')->default('low'); // low, medium, high
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_workouts');
    }
};
