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
        Schema::table('master_workouts', function (Blueprint $table) {
            $table->foreignId('coach_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_public')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_workouts', function (Blueprint $table) {
            $table->dropForeign(['coach_id']);
            $table->dropColumn(['coach_id', 'is_public']);
        });
    }
};
