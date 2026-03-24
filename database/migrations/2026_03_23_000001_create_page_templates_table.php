<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('view_path');
            $table->json('sections')->nullable(); // Define editable sections
            $table->boolean('is_active')->default(true);
            $table->boolean('is_homepage')->default(false);
            $table->timestamps();
        });

        // Add template_id to pages table
        Schema::table('pages', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->constrained('page_templates');
            $table->json('template_data')->nullable(); // Store template-specific data
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropForeign(['template_id']);
            $table->dropColumn(['template_id', 'template_data']);
        });
        
        Schema::dropIfExists('page_templates');
    }
};