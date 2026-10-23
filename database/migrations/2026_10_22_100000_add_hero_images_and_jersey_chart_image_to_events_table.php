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
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'hero_images')) {
                $table->json('hero_images')->nullable()->after('hero_image');
            }
            if (!Schema::hasColumn('events', 'jersey_chart_image')) {
                $table->string('jersey_chart_image')->nullable()->after('jersey_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'hero_images')) {
                $table->dropColumn('hero_images');
            }
            if (Schema::hasColumn('events', 'jersey_chart_image')) {
                $table->dropColumn('jersey_chart_image');
            }
        });
    }
};
