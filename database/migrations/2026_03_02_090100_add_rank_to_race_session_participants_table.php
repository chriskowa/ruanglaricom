<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('race_session_participants', 'rank')) {
            return;
        }

        Schema::table('race_session_participants', function (Blueprint $table) {
            $table->unsignedInteger('rank')->nullable()->after('result_time_ms');
            $table->index(['race_id', 'rank']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('race_session_participants', 'rank')) {
            return;
        }

        Schema::table('race_session_participants', function (Blueprint $table) {
            $table->dropIndex(['race_id', 'rank']);
            $table->dropColumn(['rank']);
        });
    }
};
