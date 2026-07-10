<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('running_analysis_artifacts', function (Blueprint $table) {
            $table->id();
            $table->char('trial_id', 36);
            $table->string('type', 30);
            $table->string('disk', 50)->default('local');
            $table->string('path', 500);
            $table->string('mime_type', 100);
            $table->string('compression', 20)->nullable();
            $table->char('sha256', 64);
            $table->unsignedBigInteger('size_bytes');
            $table->json('metadata_json')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('trial_id')->references('id')->on('running_analysis_trials')->cascadeOnDelete();
            $table->index('trial_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('running_analysis_artifacts');
    }
};
