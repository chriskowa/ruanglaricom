<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programs') && !Schema::hasColumn('programs', 'is_challenge')) {
            Schema::table('programs', function (Blueprint $table) {
                $table->boolean('is_challenge')->default(false)->after('is_published');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('programs') && Schema::hasColumn('programs', 'is_challenge')) {
            Schema::table('programs', function (Blueprint $table) {
                $table->dropColumn('is_challenge');
            });
        }
    }
};
