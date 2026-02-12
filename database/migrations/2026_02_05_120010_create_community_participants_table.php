<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_registration_id')->constrained('community_registrations')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('race_category_id')->nullable()->constrained('race_categories')->nullOnDelete();

            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('id_card')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('jersey_size')->nullable();
            $table->string('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_number')->nullable();

            $table->decimal('base_price', 12, 2)->default(0);
            $table->boolean('is_free')->default(false);
            $table->decimal('final_price', 12, 2)->default(0);

            $table->timestamps();

            $table->index(['community_registration_id', 'event_id']);
            $table->index(['event_id', 'race_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_participants');
    }
};
