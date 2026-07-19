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
        Schema::create('article_agents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('id_parent')->nullable();
            $table->string('user_input_topic')->nullable();
            $table->string('strategy')->default('free');
            $table->json('brainstorming_options')->nullable();
            $table->json('selected_option_data')->nullable();
            $table->longText('research_raw_tavily')->nullable();
            $table->longText('research_summary')->nullable();
            $table->longText('generated_article_content')->nullable();
            $table->timestamps();

            $table->foreign('id_parent')->references('id')->on('articles')->onDelete('set null');
            $table->index('id_parent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_agents');
    }
};
