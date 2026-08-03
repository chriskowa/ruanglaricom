<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('strength_exercises')) {
            Schema::create('strength_exercises', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('category'); // full_body, legs_lower_body, core, upper_body
                $table->string('equipment')->nullable(); // Bodyweight, Dumbbell, Barbell, Kettlebell, etc.
                $table->string('default_sets')->default('3');
                $table->string('default_reps')->default('10-12 reps');
                $table->enum('media_type', ['image', 'gif', 'video', 'url'])->default('gif');
                $table->string('media_url')->nullable();
                $table->text('instructions')->nullable();
                $table->string('target_muscles')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('strength_exercises');
    }
};
