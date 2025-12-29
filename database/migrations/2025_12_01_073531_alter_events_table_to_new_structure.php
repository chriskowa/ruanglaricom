<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Rename organizer_id to user_id using raw SQL (more reliable)
        if (Schema::hasColumn('events', 'organizer_id') && !Schema::hasColumn('events', 'user_id')) {
            // Drop foreign key first
            Schema::table('events', function (Blueprint $table) {
                $table->dropForeign(['organizer_id']);
            });
            
            // Rename column using raw SQL
            if (Schema::getConnection()->getDriverName() !== 'mysql') {
                DB::statement('ALTER TABLE events RENAME COLUMN organizer_id TO user_id');
            } else {
                DB::statement('ALTER TABLE events CHANGE organizer_id user_id BIGINT UNSIGNED NOT NULL');
            }
            
            // Re-add foreign key
            Schema::table('events', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } elseif (!Schema::hasColumn('events', 'user_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->after('id');
            });
        }

        // Add new columns
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'short_description')) {
                $table->text('short_description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('events', 'full_description')) {
                $table->longText('full_description')->nullable()->after('short_description');
            }
            if (!Schema::hasColumn('events', 'start_at')) {
                $table->dateTime('start_at')->nullable()->after('full_description');
            }
            if (!Schema::hasColumn('events', 'end_at')) {
                $table->dateTime('end_at')->nullable()->after('start_at');
            }
            if (!Schema::hasColumn('events', 'location_name')) {
                $table->string('location_name')->nullable()->after('end_at');
            }
            if (!Schema::hasColumn('events', 'location_address')) {
                $table->string('location_address')->nullable()->after('location_name');
            }
            if (!Schema::hasColumn('events', 'location_lat')) {
                $table->decimal('location_lat', 10, 7)->nullable()->after('location_address');
            }
            if (!Schema::hasColumn('events', 'location_lng')) {
                $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
            }
            if (!Schema::hasColumn('events', 'hero_image_url')) {
                $table->string('hero_image_url')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('events', 'map_embed_url')) {
                $table->text('map_embed_url')->nullable()->after('hero_image_url');
            }
            if (!Schema::hasColumn('events', 'google_calendar_url')) {
                $table->string('google_calendar_url')->nullable()->after('map_embed_url');
            }
        });

        // Migrate data from old structure to new if needed
        // Migrate date + time to start_at
        if (Schema::hasColumn('events', 'date') && Schema::hasColumn('events', 'time')) {
            if (Schema::getConnection()->getDriverName() !== 'mysql') {
                DB::statement("
                    UPDATE events 
                    SET start_at = date || ' ' || COALESCE(time, '00:00:00')
                    WHERE start_at IS NULL AND date IS NOT NULL
                ");
            } else {
                DB::statement("
                    UPDATE events 
                    SET start_at = CONCAT(date, ' ', COALESCE(time, '00:00:00'))
                    WHERE start_at IS NULL AND date IS NOT NULL
                ");
            }
        }
        
        // Migrate location to location_name
        if (Schema::hasColumn('events', 'location')) {
            DB::statement("
                UPDATE events 
                SET location_name = location 
                WHERE location_name IS NULL AND location IS NOT NULL
            ");
        }
        
        // Migrate description to short_description
        if (Schema::hasColumn('events', 'description')) {
            if (Schema::getConnection()->getDriverName() !== 'mysql') {
                DB::statement("
                    UPDATE events 
                    SET short_description = substr(description, 1, 500)
                    WHERE short_description IS NULL AND description IS NOT NULL
                ");
            } else {
                DB::statement("
                    UPDATE events 
                    SET short_description = LEFT(description, 500)
                    WHERE short_description IS NULL AND description IS NOT NULL
                ");
            }
        }
        
        // Migrate banner_image to hero_image_url
        if (Schema::hasColumn('events', 'banner_image')) {
            if (Schema::getConnection()->getDriverName() !== 'mysql') {
                DB::statement("
                    UPDATE events 
                    SET hero_image_url = 'storage/' || banner_image
                    WHERE hero_image_url IS NULL AND banner_image IS NOT NULL
                ");
            } else {
                DB::statement("
                    UPDATE events 
                    SET hero_image_url = CONCAT('storage/', banner_image)
                    WHERE hero_image_url IS NULL AND banner_image IS NOT NULL
                ");
            }
        }

        // Drop old columns after data migration
        Schema::table('events', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('events', 'description')) {
                $columnsToDrop[] = 'description';
            }
            if (Schema::hasColumn('events', 'date')) {
                $columnsToDrop[] = 'date';
            }
            if (Schema::hasColumn('events', 'time')) {
                $columnsToDrop[] = 'time';
            }
            if (Schema::hasColumn('events', 'location')) {
                $columnsToDrop[] = 'location';
            }
            if (Schema::hasColumn('events', 'registration_system')) {
                $columnsToDrop[] = 'registration_system';
            }
            if (Schema::hasColumn('events', 'is_active')) {
                $columnsToDrop[] = 'is_active';
            }
            if (Schema::hasColumn('events', 'banner_image')) {
                $columnsToDrop[] = 'banner_image';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // Make start_at and location_name required after migration
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'start_at')) {
                $table->dateTime('start_at')->nullable(false)->change();
            }
            if (Schema::hasColumn('events', 'location_name')) {
                $table->string('location_name')->nullable(false)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Make columns nullable before dropping
        Schema::table('events', function (Blueprint $table) {
            if (Schema::hasColumn('events', 'start_at')) {
                $table->dateTime('start_at')->nullable()->change();
            }
            if (Schema::hasColumn('events', 'location_name')) {
                $table->string('location_name')->nullable()->change();
            }
        });

        // Restore old columns
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('events', 'date')) {
                $table->date('date')->nullable()->after('description');
            }
            if (!Schema::hasColumn('events', 'time')) {
                $table->time('time')->nullable()->after('date');
            }
            if (!Schema::hasColumn('events', 'location')) {
                $table->string('location')->nullable()->after('time');
            }
            if (!Schema::hasColumn('events', 'registration_system')) {
                $table->enum('registration_system', ['direct', 'ballot'])->default('direct')->after('location');
            }
            if (!Schema::hasColumn('events', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('registration_system');
            }
            if (!Schema::hasColumn('events', 'banner_image')) {
                $table->string('banner_image')->nullable()->after('is_active');
            }
        });

        // Migrate data back (reverse)
        if (Schema::hasColumn('events', 'start_at')) {
            DB::statement("
                UPDATE events 
                SET date = DATE(start_at),
                    time = TIME(start_at)
                WHERE start_at IS NOT NULL
            ");
        }
        
        if (Schema::hasColumn('events', 'location_name')) {
            DB::statement("
                UPDATE events 
                SET location = location_name 
                WHERE location_name IS NOT NULL
            ");
        }
        
        if (Schema::hasColumn('events', 'short_description')) {
            DB::statement("
                UPDATE events 
                SET description = short_description
                WHERE short_description IS NOT NULL
            ");
        }
        
        if (Schema::hasColumn('events', 'hero_image_url')) {
            DB::statement("
                UPDATE events 
                SET banner_image = REPLACE(hero_image_url, 'storage/', '')
                WHERE hero_image_url IS NOT NULL AND hero_image_url LIKE 'storage/%'
            ");
        }

        // Drop new columns
        Schema::table('events', function (Blueprint $table) {
            $columnsToDrop = [];
            
            if (Schema::hasColumn('events', 'short_description')) {
                $columnsToDrop[] = 'short_description';
            }
            if (Schema::hasColumn('events', 'full_description')) {
                $columnsToDrop[] = 'full_description';
            }
            if (Schema::hasColumn('events', 'start_at')) {
                $columnsToDrop[] = 'start_at';
            }
            if (Schema::hasColumn('events', 'end_at')) {
                $columnsToDrop[] = 'end_at';
            }
            if (Schema::hasColumn('events', 'location_name')) {
                $columnsToDrop[] = 'location_name';
            }
            if (Schema::hasColumn('events', 'location_address')) {
                $columnsToDrop[] = 'location_address';
            }
            if (Schema::hasColumn('events', 'location_lat')) {
                $columnsToDrop[] = 'location_lat';
            }
            if (Schema::hasColumn('events', 'location_lng')) {
                $columnsToDrop[] = 'location_lng';
            }
            if (Schema::hasColumn('events', 'hero_image_url')) {
                $columnsToDrop[] = 'hero_image_url';
            }
            if (Schema::hasColumn('events', 'map_embed_url')) {
                $columnsToDrop[] = 'map_embed_url';
            }
            if (Schema::hasColumn('events', 'google_calendar_url')) {
                $columnsToDrop[] = 'google_calendar_url';
            }
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // Rename back user_id to organizer_id using raw SQL
        if (Schema::hasColumn('events', 'user_id') && !Schema::hasColumn('events', 'organizer_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
            
            if (Schema::getConnection()->getDriverName() !== 'mysql') {
                DB::statement('ALTER TABLE events RENAME COLUMN user_id TO organizer_id');
            } else {
                DB::statement('ALTER TABLE events CHANGE user_id organizer_id BIGINT UNSIGNED NOT NULL');
            }
            
            Schema::table('events', function (Blueprint $table) {
                $table->foreign('organizer_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }
};
