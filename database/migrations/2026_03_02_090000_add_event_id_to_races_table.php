<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('races', 'event_id')) {
            return;
        }

        Schema::table('races', function (Blueprint $table) {
            $table->foreignId('event_id')
                ->nullable()
                ->after('id')
                ->constrained('events')
                ->cascadeOnDelete();

            $table->index('event_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('races', 'event_id')) {
            return;
        }

        Schema::table('races', function (Blueprint $table) {
            $table->dropIndex(['event_id']);
            $table->dropConstrainedForeignId('event_id');
        });
    }
};
