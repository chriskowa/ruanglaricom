<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->unsignedInteger('ticket_email_rate_limit_per_minute')->nullable()->after('is_instant_notification');
            $table->unsignedInteger('blast_email_rate_limit_per_minute')->nullable()->after('ticket_email_rate_limit_per_minute');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['ticket_email_rate_limit_per_minute', 'blast_email_rate_limit_per_minute']);
        });
    }
};

