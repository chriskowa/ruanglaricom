<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('communities')) {
            Schema::create('communities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('pic_name');
                $table->string('pic_email');
                $table->string('pic_phone');
                $table->foreignId('city_id')->nullable()->constrained('cities')->nullOnDelete();
                $table->text('description')->nullable();
                $table->string('logo')->nullable();
                $table->foreignId('owner_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('community_members')) {
            Schema::create('community_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('community_id')->constrained('communities')->onDelete('cascade');
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('id_card')->nullable();
                $table->enum('gender', ['male', 'female'])->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('blood_type')->nullable();
                $table->string('jersey_size')->nullable();
                $table->text('address')->nullable();
                $table->string('emergency_contact_name')->nullable();
                $table->string('emergency_contact_number')->nullable();
                $table->timestamps();
                $table->index(['community_id', 'email']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('community_members');
        Schema::dropIfExists('communities');
    }
};
