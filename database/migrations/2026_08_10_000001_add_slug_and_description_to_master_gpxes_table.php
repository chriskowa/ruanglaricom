<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_gpxes', function (Blueprint $table) {
            if (! Schema::hasColumn('master_gpxes', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('title');
            }
            if (! Schema::hasColumn('master_gpxes', 'description')) {
                $table->text('description')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('master_gpxes', function (Blueprint $table) {
            if (Schema::hasColumn('master_gpxes', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('master_gpxes', 'description')) {
                $table->dropColumn('description');
            }
        });
    }
};
