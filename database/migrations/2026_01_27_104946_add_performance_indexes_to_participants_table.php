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
        Schema::table('participants', function (Blueprint $table) {
            // Composite index for finding duplicate participants in a category
            $table->index(['race_category_id', 'id_card'], 'idx_participants_category_idcard');

            // Index for filtering/searching
            $table->index('email', 'idx_participants_email');
            $table->index('id_card', 'idx_participants_idcard');
            $table->index('status', 'idx_participants_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex('idx_participants_category_idcard');
            $table->dropIndex('idx_participants_email');
            $table->dropIndex('idx_participants_idcard');
            $table->dropIndex('idx_participants_status');
        });
    }
};
