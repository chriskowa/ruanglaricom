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
        Schema::create('running_analysis_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('runner_id')->constrained('users')->onDelete('cascade');
            $table->string('runner_name');
            $table->string('runner_email');
            $table->string('focus_area'); // form, gait, injury, performance, general
            $table->text('goals')->nullable();
            $table->text('notes')->nullable();
            $table->string('preferred_location')->nullable();
            $table->date('preferred_date')->nullable();
            $table->string('status')->default('pending'); // pending, approved, scheduled, completed, rejected
            $table->text('admin_notes')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('session_id')->nullable()->constrained('running_analysis_sessions')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at'], 'ra_requests_status_created_index');
            $table->index('runner_id', 'ra_requests_runner_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('running_analysis_requests');
    }
};
