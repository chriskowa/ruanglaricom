<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('strava_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('strava_activity_id')->unique();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->timestamp('start_date')->nullable()->index();
            $table->unsignedInteger('distance_m')->nullable();
            $table->unsignedInteger('moving_time_s')->nullable();
            $table->unsignedInteger('elapsed_time_s')->nullable();
            $table->float('average_speed')->nullable();
            $table->float('total_elevation_gain')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strava_activities');
    }
};

