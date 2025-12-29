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
            $table->string('strava_id')->nullable()->after('email');
            $table->text('strava_access_token')->nullable()->after('strava_id');
            $table->text('strava_refresh_token')->nullable()->after('strava_access_token');
            $table->timestamp('strava_expires_at')->nullable()->after('strava_refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['strava_id', 'strava_access_token', 'strava_refresh_token', 'strava_expires_at']);
        });
    }
};
