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
        Schema::table('users', function (Blueprint $table) {
            $table->string('pb_5k')->nullable()->comment('HH:MM:SS');
            $table->string('pb_10k')->nullable()->comment('HH:MM:SS');
            $table->string('pb_hm')->nullable()->comment('HH:MM:SS');
            $table->string('pb_fm')->nullable()->comment('HH:MM:SS');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pb_5k', 'pb_10k', 'pb_hm', 'pb_fm']);
        });
    }
};
