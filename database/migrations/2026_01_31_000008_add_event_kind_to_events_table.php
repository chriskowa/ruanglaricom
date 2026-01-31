<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('events', 'event_kind')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->string('event_kind', 20)->default('managed')->index();
        });

        DB::table('events')
            ->where('user_id', 1)
            ->orWhereNotNull('external_registration_link')
            ->update(['event_kind' => 'directory']);
    }

    public function down(): void
    {
        if (!Schema::hasColumn('events', 'event_kind')) {
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('event_kind');
        });
    }
};
