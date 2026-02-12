<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('transactions', 'public_ref')) {
                $table->string('public_ref', 20)->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'public_ref')) {
                $table->dropUnique(['public_ref']);
                $table->dropColumn('public_ref');
            }
        });
    }
};
