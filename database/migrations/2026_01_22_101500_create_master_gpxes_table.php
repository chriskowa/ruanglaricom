<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('master_gpxes')) {
            return;
        }

        Schema::create('master_gpxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('running_event_id')->nullable()->constrained('running_events')->nullOnDelete();
            $table->string('title');
            $table->string('gpx_path');
            $table->decimal('distance_km', 8, 3)->nullable();
            $table->integer('elevation_gain_m')->nullable();
            $table->integer('elevation_loss_m')->nullable();
            $table->boolean('is_published')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['running_event_id', 'is_published']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_gpxes');
    }
};
