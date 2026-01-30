<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->unsignedTinyInteger('rating');
            $table->string('cookie_hash', 64);
            $table->string('ip_hash', 64);
            $table->string('fingerprint_hash', 64);
            $table->timestamps();

            $table->index(['event_id', 'cookie_hash']);
            $table->index(['event_id', 'ip_hash']);
            $table->index(['event_id', 'fingerprint_hash']);
            $table->index(['event_id', 'cookie_hash', 'ip_hash']);
            $table->index(['event_id', 'cookie_hash', 'fingerprint_hash']);
            $table->index(['event_id', 'ip_hash', 'fingerprint_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_ratings');
    }
};

