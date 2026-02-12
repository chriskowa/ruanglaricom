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
        Schema::table('race_session_participants', function (Blueprint $table) {
            $table->unsignedInteger('result_time_ms')->nullable()->after('predicted_time_ms');
            $table->timestamp('finished_at')->nullable()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_session_participants', function (Blueprint $table) {
            $table->dropColumn(['result_time_ms', 'finished_at']);
        });
    }
};
