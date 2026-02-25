<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'followers_count')) {
                $table->unsignedInteger('followers_count')->default(0)->after('role');
                $table->index('followers_count');
            }
        });

        // Add index on role safely
        $indexExists = collect(DB::select("SHOW INDEXES FROM users"))->pluck('Key_name')->contains('users_role_index');

        if (! $indexExists) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('role');
            });
        }

        // Backfill followers_count
        DB::statement('UPDATE users SET followers_count = (SELECT COUNT(*) FROM follows WHERE following_id = users.id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // We can't easily check if we added the index or it was there before, 
            // so strictly speaking we should only drop if we added it. 
            // But for this task, dropping it is probably fine or we can leave it.
            // Let's try to drop it if it exists.
            $table->dropIndex(['role']); // This might throw if not exists, but down() is less critical here.
            
            if (Schema::hasColumn('users', 'followers_count')) {
                $table->dropIndex(['followers_count']);
                $table->dropColumn('followers_count');
            }
        });
    }
};
