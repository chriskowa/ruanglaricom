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
        Schema::table('race_categories', function (Blueprint $table) {
            $table->foreignId('master_gpx_id')->nullable()->constrained('master_gpxes')->nullOnDelete()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_categories', function (Blueprint $table) {
            $table->dropForeign(['master_gpx_id']);
            $table->dropColumn('master_gpx_id');
        });
    }
};
