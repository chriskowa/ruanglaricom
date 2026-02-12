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
        Schema::table('pages', function (Blueprint $table) {
            $table->text('excerpt')->nullable()->after('slug');
            $table->string('featured_image')->nullable()->after('content');
            $table->string('status')->default('draft')->after('hardcoded'); // draft, published, archived
            $table->string('meta_title')->nullable()->after('status');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_keywords')->nullable()->after('meta_description');

            // Drop is_published if it exists (optional, but cleaner)
            $table->dropColumn('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['excerpt', 'featured_image', 'status', 'meta_title', 'meta_description', 'meta_keywords']);
            $table->boolean('is_published')->default(true);
        });
    }
};
