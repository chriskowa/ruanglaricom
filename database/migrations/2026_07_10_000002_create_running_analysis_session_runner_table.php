<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_analysis_session_runner', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('running_analysis_sessions')->cascadeOnDelete();
            $table->foreignId('runner_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('sequence_no')->default(0);
            $table->string('status', 30)->default('pending');
            $table->text('notes')->nullable();
            $table->boolean('consent_pose')->default(false);
            $table->boolean('consent_video')->default(false);
            $table->boolean('consent_report')->default(false);
            $table->boolean('consent_ai')->default(false);
            $table->timestamps();

            $table->unique(['session_id', 'runner_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_analysis_session_runner');
    }
};
