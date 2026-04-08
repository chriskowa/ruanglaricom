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
        Schema::create('event_email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['instant', 'absolute', 'relative'])->default('instant');
            $table->string('preset_template')->default('general'); // e.g. reminder, info, schedule, general
            $table->string('subject');
            $table->text('content')->nullable(); // JSON configuration of the blocks/fields
            
            // Scheduling
            $table->integer('offset_days')->nullable(); // For relative
            $table->time('send_time')->nullable(); // Local time
            $table->datetime('send_at')->nullable(); // For absolute
            
            // Targeting
            $table->json('filters')->nullable(); // e.g. ["payment_status": ["paid"]]
            
            $table->enum('status', ['draft', 'scheduled', 'processing', 'completed', 'paused', 'failed'])->default('draft');
            $table->integer('target_count')->default(0);
            $table->integer('sent_count')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_email_campaigns');
    }
};
