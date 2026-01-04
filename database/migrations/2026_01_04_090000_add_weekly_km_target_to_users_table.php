<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'weekly_km_target')) {
                $table->decimal('weekly_km_target', 8, 2)->nullable()->after('weekly_volume');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'weekly_km_target')) {
                $table->dropColumn('weekly_km_target');
            }
        });
    }
};
