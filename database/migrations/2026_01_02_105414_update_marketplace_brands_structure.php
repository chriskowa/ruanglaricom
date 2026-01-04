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
        // 1. Add type to marketplace_brands
        Schema::table('marketplace_brands', function (Blueprint $table) {
            $table->string('type')->nullable()->after('name'); // International, Local, Tech, etc.
        });

        // 2. Create pivot table for Category-Brand relationship
        Schema::create('marketplace_brand_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_category_id')->constrained('marketplace_categories')->onDelete('cascade');
            $table->foreignId('marketplace_brand_id')->constrained('marketplace_brands')->onDelete('cascade');
            $table->timestamps();

            // Ensure unique pairs
            $table->unique(['marketplace_category_id', 'marketplace_brand_id'], 'cat_brand_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_brand_category');

        Schema::table('marketplace_brands', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
