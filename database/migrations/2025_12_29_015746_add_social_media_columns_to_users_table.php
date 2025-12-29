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
            $table->string('strava_url')->nullable()->after('remember_token');
            $table->string('instagram_url')->nullable()->after('strava_url');
            $table->string('facebook_url')->nullable()->after('instagram_url');
            $table->string('tiktok_url')->nullable()->after('facebook_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['strava_url', 'instagram_url', 'facebook_url', 'tiktok_url']);
        });
    }
};
