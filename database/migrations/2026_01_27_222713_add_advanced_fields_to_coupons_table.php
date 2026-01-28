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
        Schema::table('coupons', function (Blueprint $table) {
            $table->decimal('min_transaction_amount', 10, 2)->default(0)->after('value');
            $table->integer('usage_limit_per_user')->nullable()->after('max_uses');
            $table->boolean('is_stackable')->default(false)->after('is_active');
            $table->json('applicable_categories')->nullable()->after('is_stackable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['min_transaction_amount', 'usage_limit_per_user', 'is_stackable', 'applicable_categories']);
        });
    }
};
