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
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('master_gpx_id')->nullable()->constrained('master_gpxes')->nullOnDelete();
            $table->string('title');
            $table->string('sport_type')->default('running');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->decimal('distance_km', 8, 2)->default(0);
            $table->unsignedInteger('moving_time_s')->default(0);
            $table->unsignedInteger('elapsed_time_s')->default(0);
            $table->unsignedInteger('avg_pace_sec')->default(0); // seconds per km
            $table->unsignedInteger('max_pace_sec')->nullable();
            $table->decimal('avg_speed_kmh', 5, 2)->default(0);
            $table->decimal('elevation_gain_m', 7, 2)->default(0);
            $table->decimal('elevation_loss_m', 7, 2)->default(0);
            $table->unsignedInteger('calories')->default(0);
            $table->longText('coordinates_json')->nullable(); // [{lat, lng, ele, time, dist}]
            $table->json('splits_json')->nullable(); // [{km, pace, split_time, cum_time, gain}]
            $table->text('notes')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('master_gpx_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activities');
    }
};
