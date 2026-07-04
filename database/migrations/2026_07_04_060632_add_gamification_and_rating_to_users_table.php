<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('run_points')->default(0)->after('is_pacer');
            $table->decimal('buddy_rating', 3, 2)->nullable()->after('run_points');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['run_points', 'buddy_rating']);
        });
    }
};
