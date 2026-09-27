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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'certifications')) {
                $table->json('certifications')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'coaching_experience')) {
                $table->text('coaching_experience')->nullable()->after('certifications');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('users', 'coaching_experience')) {
                $columnsToDrop[] = 'coaching_experience';
            }
            if (Schema::hasColumn('users', 'certifications')) {
                $columnsToDrop[] = 'certifications';
            }
            if (Schema::hasColumn('users', 'bio')) {
                $columnsToDrop[] = 'bio';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
