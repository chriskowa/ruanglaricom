<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_analysis_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->date('session_date');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->json('camera_setup_json')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->index('status');
            $table->index('session_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_analysis_sessions');
    }
};
