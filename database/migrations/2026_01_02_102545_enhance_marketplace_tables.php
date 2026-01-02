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
        // 1. Create Brands Table
        Schema::create('marketplace_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->timestamps();
        });

        // 2. Add parent_id to Categories for Sub-categories
        Schema::table('marketplace_categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('marketplace_categories')->onDelete('cascade');
        });

        // 3. Add fields to Products
        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('marketplace_brands')->onDelete('set null');
            $table->foreignId('sub_category_id')->nullable()->after('category_id')->constrained('marketplace_categories')->onDelete('set null');
            $table->string('size')->nullable()->after('type'); // S, M, L, 40, 42, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['sub_category_id']);
            $table->dropColumn(['brand_id', 'sub_category_id', 'size']);
        });

        Schema::table('marketplace_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });

        Schema::dropIfExists('marketplace_brands');
    }
};
