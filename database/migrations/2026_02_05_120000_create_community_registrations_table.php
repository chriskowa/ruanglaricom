<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('community_name');
            $table->string('pic_name');
            $table->string('pic_email');
            $table->string('pic_phone');
            $table->string('status')->default('draft');
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_registrations');
    }
};

