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
        Schema::create('photo_tagging_photo_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('photo_tagging_photo_id')->constrained('photo_tagging_photos')->cascadeOnDelete();
            $table->string('bib_number');
            $table->enum('source', ['manual', 'ocr'])->default('manual');
            $table->decimal('confidence', 5, 2)->nullable();
            $table->timestamps();
            
            $table->index('bib_number');
            $table->unique(['photo_tagging_photo_id', 'bib_number'], 'photo_tag_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_tagging_photo_tags');
    }
};
