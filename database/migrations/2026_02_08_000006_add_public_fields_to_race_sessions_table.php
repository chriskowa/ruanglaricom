<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_sessions', function (Blueprint $table) {
            $table->string('slug', 64)->nullable()->unique()->after('race_id');
            $table->string('category', 100)->nullable()->after('slug');
            $table->decimal('distance_km', 8, 3)->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('race_sessions', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'category', 'distance_km']);
        });
    }
};
