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
        // Skip for SQLite testing environment
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        // Strategy to handle ENUM change with data migration:
        // 1. Change to VARCHAR to allow intermediate values
        DB::statement("ALTER TABLE users MODIFY COLUMN package_tier VARCHAR(255) DEFAULT 'basic'");
        
        // 2. Migrate data
        DB::table('users')->where('package_tier', 'business')->update(['package_tier' => 'elite']);
        
        // 3. Apply new ENUM definition
        DB::statement("ALTER TABLE users MODIFY COLUMN package_tier ENUM('basic', 'lite', 'pro', 'elite') DEFAULT 'basic'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Skip for SQLite testing environment
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN package_tier ENUM('basic', 'pro', 'business') DEFAULT 'basic'");
    }
};
