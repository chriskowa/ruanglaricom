<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->default('pending')->index();

            $table->string('event_name');
            $table->date('event_date');
            $table->time('start_time')->nullable();

            $table->string('location_name');
            $table->string('location_address', 500)->nullable();

            $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
            $table->string('city_text')->nullable();

            $table->foreignId('race_type_id')->nullable()->constrained('race_types')->nullOnDelete();
            $table->json('race_distance_ids')->nullable();

            $table->string('registration_link')->nullable();
            $table->string('social_media_link')->nullable();

            $table->string('organizer_name')->nullable();
            $table->string('organizer_contact')->nullable();

            $table->string('contributor_name')->nullable();
            $table->string('contributor_email');
            $table->string('contributor_phone', 30)->nullable();

            $table->text('notes')->nullable();

            $table->string('fingerprint', 64)->index();
            $table->string('ip_hash', 64)->nullable();
            $table->string('ua_hash', 64)->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_submissions');
    }
};

