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
        if (Schema::hasTable('race_results')) {
            return;
        }
        
        Schema::create('race_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('race_category_id')->nullable()->constrained('race_categories')->onDelete('set null');
            $table->string('bib_number', 50);
            $table->string('runner_name');
            $table->enum('gender', ['M', 'F']);
            $table->string('nationality', 10)->default('IDN');
            $table->string('category_code', 20)->nullable(); // FM, HM, 10K, etc
            $table->time('gun_time')->nullable(); // Waktu mulai (Gun Time)
            $table->time('chip_time')->nullable(); // Waktu finish (Chip Time)
            $table->string('pace', 10)->nullable(); // Pace per km (format: MM:SS)
            $table->integer('rank_overall')->nullable(); // Ranking keseluruhan
            $table->integer('rank_category')->nullable(); // Ranking per kategori
            $table->integer('rank_gender')->nullable(); // Ranking per gender dalam kategori
            $table->boolean('is_podium')->default(false); // Flag untuk juara 1-3
            $table->string('podium_position')->nullable(); // 1, 2, 3 atau null
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes untuk performa query
            $table->index('event_id');
            $table->index('race_category_id');
            $table->index('bib_number');
            $table->index(['event_id', 'race_category_id', 'rank_category']);
            $table->index(['event_id', 'category_code', 'gender', 'rank_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_results');
    }
};



