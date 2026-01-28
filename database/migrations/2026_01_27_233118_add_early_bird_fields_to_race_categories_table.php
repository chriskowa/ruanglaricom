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
        Schema::table('race_categories', function (Blueprint $table) {
            $table->unsignedInteger('early_bird_quota')->nullable()->after('price_early');
            $table->dateTime('early_bird_end_at')->nullable()->after('early_bird_quota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_categories', function (Blueprint $table) {
            $table->dropColumn(['early_bird_quota', 'early_bird_end_at']);
        });
    }
};
