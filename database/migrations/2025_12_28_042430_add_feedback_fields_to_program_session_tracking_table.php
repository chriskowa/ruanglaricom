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
            $table->text('coach_feedback')->nullable()->after('notes');
            $table->unsignedTinyInteger('coach_rating')->nullable()->after('coach_feedback'); // 1-5
            $table->unsignedTinyInteger('rpe')->nullable()->after('coach_rating'); // 1-10
            $table->string('feeling')->nullable()->after('rpe'); // strong, good, average, weak, terrible
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_session_tracking', function (Blueprint $table) {
            $table->dropColumn(['coach_feedback', 'coach_rating', 'rpe', 'feeling']);
        });
    }
};
