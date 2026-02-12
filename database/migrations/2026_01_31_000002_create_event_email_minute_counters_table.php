<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_email_minute_counters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->timestamp('minute_at')->index();
            $table->unsignedInteger('reserved_emails')->default(0);
            $table->timestamps();

            $table->unique(['event_id', 'minute_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_email_minute_counters');
    }
};
