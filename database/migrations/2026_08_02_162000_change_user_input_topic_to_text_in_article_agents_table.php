<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('article_agents')) {
            try {
                DB::statement('ALTER TABLE article_agents MODIFY user_input_topic TEXT NULL');
            } catch (\Throwable $e) {
                // Fallback for driver differences
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('article_agents')) {
            try {
                DB::statement('ALTER TABLE article_agents MODIFY user_input_topic VARCHAR(255) NULL');
            } catch (\Throwable $e) {
            }
        }
    }
};
