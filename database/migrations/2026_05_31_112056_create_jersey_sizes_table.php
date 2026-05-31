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
        Schema::create('jersey_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->onDelete('cascade');
            $table->integer('xxs')->nullable()->default(null);
            $table->integer('xs')->nullable()->default(null);
            $table->integer('s')->nullable()->default(null);
            $table->integer('m')->nullable()->default(null);
            $table->integer('l')->nullable()->default(null);
            $table->integer('xl')->nullable()->default(null);
            $table->integer('2xl')->nullable()->default(null);
            $table->integer('3xl')->nullable()->default(null);
            $table->integer('4xl')->nullable()->default(null);
            $table->integer('5xl')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jersey_sizes');
    }
};
