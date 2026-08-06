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
        Schema::table('carts', function (Blueprint $table) {
            if (!Schema::hasColumn('carts', 'product_id')) {
                $table->foreignId('product_id')->nullable()->after('user_id')->constrained('marketplace_products')->onDelete('cascade');
            }
        });

        // Make program_id nullable so carts can hold either a program or a marketplace product
        DB::statement('ALTER TABLE carts MODIFY program_id BIGINT UNSIGNED NULL');

        // Drop old unique index if exists and add non-strict checks
        try {
            Schema::table('carts', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'program_id']);
            });
        } catch (\Exception $e) {
            // Ignore if index doesn't exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            if (Schema::hasColumn('carts', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }
        });
    }
};
