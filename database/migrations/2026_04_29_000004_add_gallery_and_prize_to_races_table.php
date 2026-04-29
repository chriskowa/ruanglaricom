<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('races', function (Blueprint $table) {
            if (! Schema::hasColumn('races', 'gallery_paths')) {
                $table->json('gallery_paths')->nullable()->after('banner_path');
            }

            if (! Schema::hasColumn('races', 'prize_info')) {
                $table->text('prize_info')->nullable()->after('gallery_paths');
            }
        });
    }

    public function down(): void
    {
        Schema::table('races', function (Blueprint $table) {
            if (Schema::hasColumn('races', 'prize_info')) {
                $table->dropColumn('prize_info');
            }
            if (Schema::hasColumn('races', 'gallery_paths')) {
                $table->dropColumn('gallery_paths');
            }
        });
    }
};
