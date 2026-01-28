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
            $table->timestamp('start_at')->nullable()->after('used_count');
            $table->unsignedBigInteger('event_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('start_at');
            // We cannot easily revert nullable to not null if there are null values,
            // but for down method we assume we want to revert structure.
            // We might need to delete global coupons first or this will fail.
            $table->unsignedBigInteger('event_id')->nullable(false)->change();
        });
    }
};
