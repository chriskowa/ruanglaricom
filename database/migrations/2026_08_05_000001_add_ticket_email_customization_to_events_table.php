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
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'ticket_email_template')) {
                $table->string('ticket_email_template')->default('modern')->after('ticket_email_use_qr');
            }
            if (! Schema::hasColumn('events', 'ticket_email_show_jersey')) {
                $table->boolean('ticket_email_show_jersey')->default(true)->after('ticket_email_template');
            }
            if (! Schema::hasColumn('events', 'ticket_email_show_addons')) {
                $table->boolean('ticket_email_show_addons')->default(true)->after('ticket_email_show_jersey');
            }
            if (! Schema::hasColumn('events', 'ticket_email_show_pic')) {
                $table->boolean('ticket_email_show_pic')->default(true)->after('ticket_email_show_addons');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('events', 'ticket_email_template')) {
                $columns[] = 'ticket_email_template';
            }
            if (Schema::hasColumn('events', 'ticket_email_show_jersey')) {
                $columns[] = 'ticket_email_show_jersey';
            }
            if (Schema::hasColumn('events', 'ticket_email_show_addons')) {
                $columns[] = 'ticket_email_show_addons';
            }
            if (Schema::hasColumn('events', 'ticket_email_show_pic')) {
                $columns[] = 'ticket_email_show_pic';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
