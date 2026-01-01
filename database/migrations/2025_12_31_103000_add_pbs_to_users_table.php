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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pb_5k')) {
                $table->string('pb_5k')->nullable()->after('is_pacer');
            }
            if (!Schema::hasColumn('users', 'pb_10k')) {
                $table->string('pb_10k')->nullable()->after('pb_5k');
            }
            if (!Schema::hasColumn('users', 'pb_hm')) {
                $table->string('pb_hm')->nullable()->after('pb_10k');
            }
            if (!Schema::hasColumn('users', 'pb_fm')) {
                $table->string('pb_fm')->nullable()->after('pb_hm');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pb_5k', 'pb_10k', 'pb_hm', 'pb_fm']);
        });
    }
};
