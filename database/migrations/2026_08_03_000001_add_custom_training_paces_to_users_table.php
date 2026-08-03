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
        if (!Schema::hasColumn('users', 'custom_training_paces')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('custom_training_paces')->nullable()->after('weekly_km_target');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'custom_training_paces')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('custom_training_paces');
            });
        }
    }
};
