<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            if (! Schema::hasColumn('marketplace_products', 'reserved_by_user_id')) {
                $table->foreignId('reserved_by_user_id')->nullable()->after('stock')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('marketplace_products', 'reserved_until')) {
                $table->timestamp('reserved_until')->nullable()->after('reserved_by_user_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            if (Schema::hasColumn('marketplace_products', 'reserved_by_user_id')) {
                $table->dropForeign(['reserved_by_user_id']);
            }
            $table->dropColumn([
                'reserved_by_user_id',
                'reserved_until',
            ]);
        });
    }
};
