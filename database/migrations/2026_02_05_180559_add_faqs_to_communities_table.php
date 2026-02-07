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
        Schema::table('communities', function (Blueprint $table) {
            if (! Schema::hasColumn('communities', 'faqs')) {
                $table->json('faqs')->nullable()->after('captains');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            if (Schema::hasColumn('communities', 'faqs')) {
                $table->dropColumn('faqs');
            }
        });
    }
};
