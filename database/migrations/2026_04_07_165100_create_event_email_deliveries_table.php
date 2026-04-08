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
        Schema::create('event_email_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_email_campaign_id')->constrained('event_email_campaigns')->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('participants')->nullOnDelete();
            
            $table->string('to_email');
            $table->string('to_name')->nullable();
            
            $table->enum('status', ['pending', 'queued', 'sent', 'failed', 'skipped'])->default('pending');
            $table->datetime('scheduled_at')->nullable();
            $table->datetime('sent_at')->nullable();
            
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            
            $table->timestamps();
            
            // Prevent duplicate sending for the same campaign + email
            $table->unique(['event_email_campaign_id', 'to_email'], 'idx_campaign_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_email_deliveries');
    }
};
