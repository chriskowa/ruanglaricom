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
        Schema::table('program_session_tracking', function (Blueprint $table) {
            $table->string('strava_link')->nullable()->after('status');
            $table->text('notes')->nullable()->after('strava_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_session_tracking', function (Blueprint $table) {
            $table->dropColumn(['strava_link', 'notes']);
        });
    }
};
