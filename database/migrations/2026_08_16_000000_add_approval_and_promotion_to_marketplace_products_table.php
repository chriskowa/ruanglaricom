<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            if (! Schema::hasColumn('marketplace_products', 'is_approved')) {
                $table->boolean('is_approved')->default(true);
            }
            if (! Schema::hasColumn('marketplace_products', 'approval_status')) {
                $table->string('approval_status')->default('approved');
            }
            if (! Schema::hasColumn('marketplace_products', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            if (! Schema::hasColumn('marketplace_products', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
            }
            if (! Schema::hasColumn('marketplace_products', 'featured_until')) {
                $table->timestamp('featured_until')->nullable();
            }
            if (! Schema::hasColumn('marketplace_products', 'boosted_at')) {
                $table->timestamp('boosted_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['is_approved', 'approval_status', 'rejection_reason', 'is_featured', 'featured_until', 'boosted_at'] as $column) {
                if (Schema::hasColumn('marketplace_products', $column)) {
                    $columnsToDrop[] = $column;
                }
            }
            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
