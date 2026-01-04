<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add phone to users if missing
        if (! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone')->nullable()->index();
            });
        }
        Schema::create('pacers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('seo_slug')->unique();
            $table->string('nickname')->nullable();
            $table->string('category')->nullable();
            $table->string('pace')->nullable();
            $table->string('image_url')->nullable();
            $table->string('whatsapp')->nullable();
            $table->boolean('verified')->default(false);
            $table->unsignedInteger('total_races')->default(0);
            $table->text('bio')->nullable();
            $table->json('stats')->nullable();
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('phone');
            });
        }
        Schema::dropIfExists('pacers');
    }
};
