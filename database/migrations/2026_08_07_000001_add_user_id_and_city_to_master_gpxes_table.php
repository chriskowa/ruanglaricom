<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_gpxes', function (Blueprint $table) {
            if (! Schema::hasColumn('master_gpxes', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('master_gpxes', 'city')) {
                $table->string('city')->nullable();
            }
            if (! Schema::hasColumn('master_gpxes', 'coordinates_json')) {
                $table->longText('coordinates_json')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('master_gpxes', function (Blueprint $table) {
            if (Schema::hasColumn('master_gpxes', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('master_gpxes', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('master_gpxes', 'coordinates_json')) {
                $table->dropColumn('coordinates_json');
            }
        });
    }
};
