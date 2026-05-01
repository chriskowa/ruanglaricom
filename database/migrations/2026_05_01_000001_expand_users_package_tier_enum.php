<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN package_tier VARCHAR(255) DEFAULT 'basic'");

        DB::table('users')->where('package_tier', 'business')->update(['package_tier' => 'elite']);

        DB::statement("ALTER TABLE users MODIFY COLUMN package_tier ENUM('basic', 'lite', 'pro', 'premium', 'elite') DEFAULT 'basic'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY COLUMN package_tier VARCHAR(255) DEFAULT 'basic'");

        DB::table('users')->where('package_tier', 'premium')->update(['package_tier' => 'elite']);

        DB::statement("ALTER TABLE users MODIFY COLUMN package_tier ENUM('basic', 'lite', 'pro', 'elite') DEFAULT 'basic'");
    }
};
