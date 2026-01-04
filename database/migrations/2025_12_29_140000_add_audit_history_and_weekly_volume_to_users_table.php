<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'audit_history')) {
                $table->json('audit_history')->nullable()->after('google_calendar_token');
            }
            if (! Schema::hasColumn('users', 'weekly_volume')) {
                $table->decimal('weekly_volume', 8, 2)->nullable()->after('audit_history');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'audit_history')) {
                $table->dropColumn('audit_history');
            }
            if (Schema::hasColumn('users', 'weekly_volume')) {
                $table->dropColumn('weekly_volume');
            }
        });
    }
};
