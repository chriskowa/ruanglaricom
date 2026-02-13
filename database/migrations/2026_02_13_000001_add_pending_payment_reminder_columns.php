<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamp('pending_reminder_last_sent_at')->nullable()->after('updated_at');
            $table->unsignedInteger('pending_reminder_count')->default(0)->after('pending_reminder_last_sent_at');
            $table->string('pending_reminder_last_channel')->nullable()->after('pending_reminder_count');
        });

        Schema::table('event_email_delivery_logs', function (Blueprint $table) {
             $table->string('context')->nullable()->after('transaction_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['pending_reminder_last_sent_at', 'pending_reminder_count', 'pending_reminder_last_channel']);
        });

        Schema::table('event_email_delivery_logs', function (Blueprint $table) {
            $table->dropColumn('context');
        });
    }
};
