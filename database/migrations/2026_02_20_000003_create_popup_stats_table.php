<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popup_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('popup_id');
            $table->date('stat_date');
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('conversions')->default(0);
            $table->timestamps();
            $table->unique(['popup_id', 'stat_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_stats');
    }
};
