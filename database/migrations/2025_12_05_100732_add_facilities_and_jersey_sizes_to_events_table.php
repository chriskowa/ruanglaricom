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
            // Fasilitas event (JSON untuk checklist dan deskripsi)
            if (!Schema::hasColumn('events', 'facilities')) {
                $table->json('facilities')->nullable()->after('promo_code');
            }
            
            // Ukuran jersey yang tersedia (JSON array)
            if (!Schema::hasColumn('events', 'jersey_sizes')) {
                $table->json('jersey_sizes')->nullable()->after('facilities');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'jersey_sizes')) {
                $table->dropColumn('jersey_sizes');
            }
            if (Schema::hasColumn('events', 'facilities')) {
                $table->dropColumn('facilities');
            }
        });
    }
};
