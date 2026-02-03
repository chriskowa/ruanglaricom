<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->string('address', 500)->nullable()->after('id_card');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->json('sheets_config')->nullable()->after('whatsapp_config');
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn('address');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('sheets_config');
        });
    }
};

