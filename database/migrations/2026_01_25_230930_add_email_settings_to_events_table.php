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
            $table->text('custom_email_message')->nullable()->comment('Custom message to be displayed in registration email');
            $table->boolean('is_instant_notification')->default(false)->comment('If true, send emails immediately without queue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['custom_email_message', 'is_instant_notification']);
        });
    }
};
