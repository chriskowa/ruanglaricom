<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('race_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('race_sessions', 'quota')) {
                $table->unsignedInteger('quota')->nullable()->after('distance_km');
            }

            if (! Schema::hasColumn('race_sessions', 'bib_start')) {
                $table->unsignedInteger('bib_start')->nullable()->after('quota');
            }

            if (! Schema::hasColumn('race_sessions', 'bib_prefix')) {
                $table->string('bib_prefix', 12)->nullable()->after('bib_start');
            }
        });
    }

    public function down(): void
    {
        Schema::table('race_sessions', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('race_sessions', 'bib_prefix')) $cols[] = 'bib_prefix';
            if (Schema::hasColumn('race_sessions', 'bib_start')) $cols[] = 'bib_start';
            if (Schema::hasColumn('race_sessions', 'quota')) $cols[] = 'quota';
            if ($cols) $table->dropColumn($cols);
        });
    }
};

