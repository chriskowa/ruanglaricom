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
        Schema::create('eo_email_blast_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eo_email_blast_id')->constrained('eo_email_blasts')->cascadeOnDelete();
            $table->string('to_email');
            $table->string('to_name')->nullable();
            $table->json('payload')->nullable(); // entire csv row for placeholders
            $table->string('rendered_subject')->nullable();
            $table->string('status')->default('pending'); // pending, queued, sent, failed, skipped
            $table->integer('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['eo_email_blast_id', 'to_email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eo_email_blast_deliveries');
    }
};
