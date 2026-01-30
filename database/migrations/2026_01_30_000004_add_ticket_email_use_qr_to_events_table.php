<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'ticket_email_use_qr')) {
                $table->boolean('ticket_email_use_qr')
                    ->default(true)
                    ->after('custom_email_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'ticket_email_use_qr')) {
                $table->dropColumn('ticket_email_use_qr');
            }
        });
    }
};

