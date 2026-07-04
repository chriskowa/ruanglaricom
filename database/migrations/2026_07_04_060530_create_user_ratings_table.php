<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('run_thread_id')->constrained('run_threads')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned(); // 1 to 5
            $table->text('comment')->nullable();
            $table->timestamps();

            // Prevent duplicate ratings for the same thread between same users
            $table->unique(['reviewer_id', 'reviewee_id', 'run_thread_id'], 'unique_thread_rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_ratings');
    }
};
