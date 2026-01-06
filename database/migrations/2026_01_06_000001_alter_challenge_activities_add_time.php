<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_activities', function (Blueprint $table) {
            if (! Schema::hasColumn('challenge_activities', 'activity_time')) {
                $table->time('activity_time')->nullable()->after('date');
            }
        });

        Schema::table('challenge_activities', function (Blueprint $table) {
            $table->index('user_id');
            $table->dropUnique('challenge_activities_user_id_date_unique');
            $table->unique(['user_id', 'date', 'activity_time']);
        });
    }

    public function down(): void
    {
        Schema::table('challenge_activities', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'date', 'activity_time']);
            $table->unique(['user_id', 'date']);
        });

        Schema::table('challenge_activities', function (Blueprint $table) {
            if (Schema::hasColumn('challenge_activities', 'activity_time')) {
                $table->dropColumn('activity_time');
            }
        });
    }
};
