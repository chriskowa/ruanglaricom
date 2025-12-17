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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coach_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->enum('distance_target', ['5k', '10k', '21k', '42k', 'fm'])->default('5k');
            $table->time('target_time')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->json('program_json'); // Sessions data dalam JSON
            $table->boolean('is_vdot_generated')->default(false);
            $table->decimal('vdot_score', 4, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
