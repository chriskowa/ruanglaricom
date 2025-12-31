<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leaderboard_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('active_days')->default(0);
            $table->unsignedInteger('percentage')->default(0);
            $table->unsignedInteger('streak')->default(0);
            $table->boolean('qualified')->default(false);
            $table->date('last_active_date')->nullable();
            $table->string('old_pb')->nullable();
            $table->string('new_pb')->nullable();
            $table->integer('gap_seconds')->default(0);
            $table->string('gap')->nullable();
            $table->string('pace')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_stats');
    }
};
