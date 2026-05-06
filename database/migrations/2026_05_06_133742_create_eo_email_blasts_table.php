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
        Schema::create('eo_email_blasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('eo_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->string('name');
            $table->string('subject_template');
            $table->longText('html_template');
            $table->string('source_type')->default('single'); // single, csv
            $table->string('csv_original_name')->nullable();
            $table->string('csv_path')->nullable();
            $table->string('email_column')->nullable();
            $table->string('name_column')->nullable();
            $table->string('status')->default('draft'); // draft, processing, completed, failed, paused
            $table->integer('target_count')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eo_email_blasts');
    }
};
