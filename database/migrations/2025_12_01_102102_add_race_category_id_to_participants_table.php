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
            $table->foreignId('race_category_id')->nullable()->after('event_package_id')->constrained('race_categories')->onDelete('cascade');
            $table->index('race_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropForeign(['race_category_id']);
            $table->dropIndex(['race_category_id']);
            $table->dropColumn('race_category_id');
        });
    }
};
