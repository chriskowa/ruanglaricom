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
        Schema::create('race_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('name');
            $table->decimal('distance_km', 5, 2)->nullable();
            $table->string('code')->nullable();
            $table->integer('quota')->nullable();
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->integer('cutoff_minutes')->nullable();
            $table->unsignedInteger('price_early')->nullable();
            $table->unsignedInteger('price_regular')->nullable();
            $table->unsignedInteger('price_late')->nullable();
            $table->dateTime('reg_start_at')->nullable();
            $table->dateTime('reg_end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_categories');
    }
};
