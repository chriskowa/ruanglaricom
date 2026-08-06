<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('marketplace_products', 'views_count')) {
            Schema::table('marketplace_products', function (Blueprint $table) {
                $table->unsignedBigInteger('views_count')->default(0)->after('is_active');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('marketplace_products', 'views_count')) {
            Schema::table('marketplace_products', function (Blueprint $table) {
                $table->dropColumn('views_count');
            });
        }
    }
};
