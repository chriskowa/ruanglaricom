<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master: Jenis Lomba (Fun Run, Race, Trail, Ultra, etc.)
        Schema::create('race_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // Master: Kategori Jarak (5K, 10K, HM, FM, etc.)
        Schema::create('race_distances', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "10K"
            $table->string('slug')->unique();
            $table->integer('distance_meter')->nullable(); // e.g., 10000
            $table->timestamps();
        });

        // Main Table: Running Events
        Schema::create('running_events', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama Event
            $table->string('slug')->unique(); // SEO URL

            $table->string('banner_image')->nullable(); // Upload or URL
            $table->text('description')->nullable();

            $table->date('event_date'); // Tanggal Pelaksanaan
            $table->time('start_time')->nullable(); // Jam & Menit

            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('location_name')->nullable(); // Lokasi spesifik text jika city kurang detail

            $table->foreignId('race_type_id')->nullable()->constrained('race_types')->nullOnDelete();

            $table->string('registration_link')->nullable();
            $table->string('social_media_link')->nullable();

            $table->string('organizer_name')->nullable(); // Penyelenggara / EO
            $table->string('organizer_contact')->nullable();
            $table->string('contributor_contact')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            $table->timestamps();
            $table->softDeletes();
        });

        // Pivot: Event <-> Distances (Many-to-Many)
        // Satu event bisa punya banyak kategori jarak (misal: 5K, 10K, HM)
        Schema::create('running_event_distances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('running_event_id')->constrained('running_events')->cascadeOnDelete();
            $table->foreignId('race_distance_id')->constrained('race_distances')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['running_event_id', 'race_distance_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_event_distances');
        Schema::dropIfExists('running_events');
        Schema::dropIfExists('race_distances');
        Schema::dropIfExists('race_types');
    }
};
