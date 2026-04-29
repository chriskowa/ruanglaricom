<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_session_participants', function (Blueprint $table) {
            if (! Schema::hasColumn('race_session_participants', 'race_session_id')) {
                $table->foreignId('race_session_id')
                    ->nullable()
                    ->after('race_id')
                    ->constrained('race_sessions')
                    ->nullOnDelete();

                $table->index(['race_id', 'race_session_id']);
            }

            if (! Schema::hasColumn('race_session_participants', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('participant_id')
                    ->constrained('users')
                    ->nullOnDelete();

                $table->index(['race_id', 'user_id']);
            }
        });

        Schema::table('race_session_participants', function (Blueprint $table) {
            if (! Schema::hasColumn('race_session_participants', 'user_id')) {
                return;
            }

            if (Schema::hasColumn('race_session_participants', 'race_session_id')) {
                $table->unique(['race_session_id', 'user_id'], 'u_rsp_session_user');
            }
        });
    }

    public function down(): void
    {
        Schema::table('race_session_participants', function (Blueprint $table) {
            if (Schema::hasColumn('race_session_participants', 'race_session_id')) {
                $table->dropIndex(['race_id', 'race_session_id']);
                $table->dropConstrainedForeignId('race_session_id');
            }

            if (Schema::hasColumn('race_session_participants', 'user_id')) {
                if (Schema::hasColumn('race_session_participants', 'race_session_id')) {
                    $table->dropUnique('u_rsp_session_user');
                }
                $table->dropIndex(['race_id', 'user_id']);
                $table->dropConstrainedForeignId('user_id');
            }
        });
    }
};
