<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popup_versions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('popup_id');
            $table->unsignedInteger('version');
            $table->json('payload');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->unique(['popup_id', 'version']);
            $table->index(['popup_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_versions');
    }
};
