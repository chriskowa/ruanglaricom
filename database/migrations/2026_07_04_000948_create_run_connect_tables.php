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
        Schema::create('run_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('community_id')->nullable()->constrained('communities')->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // Casual Run, Long Run, Speed Session, Recovery Run, Race Prep, Community Run
            $table->decimal('run_distance_km', 8, 2);
            $table->string('pace_min')->nullable();
            $table->string('pace_max')->nullable();
            $table->date('start_date');
            $table->time('start_time');
            $table->string('start_location_name');
            $table->double('start_latitude');
            $table->double('start_longitude');
            $table->string('route_url')->nullable();
            $table->string('gpx_file_path')->nullable();
            $table->integer('quota');
            $table->string('status')->default('open'); // open, full, started, completed, cancelled
            $table->string('visibility')->default('public'); // public, community
            $table->boolean('is_beginner_friendly')->default(false);
            $table->boolean('is_women_friendly')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indices for geospatial queries and filtering
            $table->index(['start_latitude', 'start_longitude']);
            $table->index('status');
            $table->index('start_date');
        });

        Schema::create('run_thread_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_thread_id')->constrained('run_threads')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('joined'); // joined, left, removed
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['run_thread_id', 'user_id']);
        });

        Schema::create('run_thread_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('run_thread_id')->constrained('run_threads')->onDelete('cascade');
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->string('reason');
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, reviewed, resolved
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('run_thread_reports');
        Schema::dropIfExists('run_thread_participants');
        Schema::dropIfExists('run_threads');
    }
};
