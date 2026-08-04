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
        if (Schema::hasTable('whatsapp_logs')) {
            Schema::table('whatsapp_logs', function (Blueprint $table) {
                if (! Schema::hasColumn('whatsapp_logs', 'gateway_name')) {
                    $table->string('gateway_name')->nullable()->after('to');
                }
                if (! Schema::hasColumn('whatsapp_logs', 'category')) {
                    $table->string('category')->nullable()->after('gateway_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('whatsapp_logs')) {
            Schema::table('whatsapp_logs', function (Blueprint $table) {
                if (Schema::hasColumn('whatsapp_logs', 'gateway_name')) {
                    $table->dropColumn('gateway_name');
                }
                if (Schema::hasColumn('whatsapp_logs', 'category')) {
                    $table->dropColumn('category');
                }
            });
        }
    }
};
