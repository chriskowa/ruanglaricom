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
        Schema::table('programs', function (Blueprint $table) {
            // Marketplace fields
            $table->string('thumbnail')->nullable()->after('description');
            $table->string('banner')->nullable()->after('thumbnail');
            $table->boolean('is_published')->default(false)->after('is_active');
            $table->integer('duration_weeks')->nullable()->after('distance_target');
            $table->integer('enrolled_count')->default(0)->after('is_published');
            $table->decimal('average_rating', 3, 2)->default(0)->after('enrolled_count');
            $table->integer('total_reviews')->default(0)->after('average_rating');
            
            // Self-generated program fields (for Daniels Formula)
            $table->boolean('is_self_generated')->default(false)->after('total_reviews');
            $table->json('daniels_params')->nullable()->after('is_self_generated');
            $table->decimal('generated_vdot', 4, 2)->nullable()->after('daniels_params');
        });

        // Update distance_target enum - remove 'fm' and ensure consistency
        // Note: Laravel doesn't support direct enum modification, so we'll handle this in application logic
        // Or we can drop and recreate if needed, but for now we'll keep existing values
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn([
                'thumbnail',
                'banner',
                'is_published',
                'duration_weeks',
                'enrolled_count',
                'average_rating',
                'total_reviews',
                'is_self_generated',
                'daniels_params',
                'generated_vdot',
            ]);
        });
    }
};
