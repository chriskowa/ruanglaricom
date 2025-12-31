<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'program')) {
                    $table->string('program')->nullable()->index();
                }
                if (!Schema::hasColumn('users', 'baseline_5k')) {
                    $table->integer('baseline_5k')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'program')) {
                    $table->dropColumn('program');
                }
                if (Schema::hasColumn('users', 'baseline_5k')) {
                    $table->dropColumn('baseline_5k');
                }
            });
        }
    }
};
