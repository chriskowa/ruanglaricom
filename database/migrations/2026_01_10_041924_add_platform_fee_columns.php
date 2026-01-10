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
        Schema::table('events', function (Blueprint $table) {
            $table->decimal('platform_fee', 10, 2)->default(0)->after('template');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('admin_fee', 10, 2)->default(0)->after('discount_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('platform_fee');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('admin_fee');
        });
    }
};
