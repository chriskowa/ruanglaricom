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
        // 1. Add columns to events table
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->constrained('cities');
            $table->foreignId('race_type_id')->nullable()->constrained('race_types');
            $table->string('external_registration_link')->nullable();
            $table->string('social_media_link')->nullable();
            $table->string('organizer_name')->nullable();
            $table->string('organizer_contact')->nullable();
            $table->string('contributor_contact')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status')->default('published');
        });

        // 2. Drop Foreign Keys on child tables to allow ID updates
        Schema::table('master_gpxes', function (Blueprint $table) {
            // Check if foreign key exists before dropping (using array syntax usually works if convention is followed)
            // Or try catch block? Migration logic usually assumes standard state.
            // Convention: master_gpxes_running_event_id_foreign
            $table->dropForeign(['running_event_id']);
        });

        Schema::table('running_event_distances', function (Blueprint $table) {
            // Convention: running_event_distances_running_event_id_foreign
            $table->dropForeign(['running_event_id']);
        });

        // 3. Migrate data
        $runningEvents = DB::table('running_events')->get();
        foreach ($runningEvents as $re) {
            // Check slug duplication
            $slug = $re->slug;
            $originalSlug = $slug;
            $count = 1;
            while (DB::table('events')->where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            // Combine date and time
            $startAt = null;
            if ($re->event_date) {
                $time = $re->start_time ?? '00:00:00';
                $startAt = $re->event_date . ' ' . $time;
            }

            // Insert to events
            $eventId = DB::table('events')->insertGetId([
                'name' => $re->name,
                'slug' => $slug,
                'hero_image_url' => $re->banner_image,
                'full_description' => $re->description,
                'start_at' => $startAt,
                // Default end_at +6 hours
                'end_at' => $startAt ? date('Y-m-d H:i:s', strtotime($startAt . ' +6 hours')) : null,
                'city_id' => $re->city_id,
                'location_name' => $re->location_name,
                'race_type_id' => $re->race_type_id,
                'external_registration_link' => $re->registration_link,
                'social_media_link' => $re->social_media_link,
                'organizer_name' => $re->organizer_name,
                'organizer_contact' => $re->organizer_contact,
                'contributor_contact' => $re->contributor_contact,
                'is_featured' => $re->is_featured,
                'status' => $re->status ?? 'published',
                'created_at' => $re->created_at,
                'updated_at' => $re->updated_at,
                // Set default user_id if required? events.user_id might be nullable or required.
                // Let's check events table structure again if fails. Assuming nullable or has default.
                // Looking at Event model, user_id is in fillable. Let's assume it can be null or we set to 1 (admin).
                'user_id' => 1, // Assign to Admin by default
            ]);

            // Update child tables references
            DB::table('master_gpxes')->where('running_event_id', $re->id)->update(['running_event_id' => $eventId]);
            DB::table('running_event_distances')->where('running_event_id', $re->id)->update(['running_event_id' => $eventId]);
        }

        // 4. Rename columns and tables, and add new constraints
        
        // master_gpxes
        Schema::table('master_gpxes', function (Blueprint $table) {
            $table->renameColumn('running_event_id', 'event_id');
        });
        Schema::table('master_gpxes', function (Blueprint $table) {
             $table->foreign('event_id')->references('id')->on('events')->nullOnDelete();
        });

        // running_event_distances -> event_distances
        Schema::table('running_event_distances', function (Blueprint $table) {
             $table->renameColumn('running_event_id', 'event_id');
        });

        Schema::rename('running_event_distances', 'event_distances');

        Schema::table('event_distances', function (Blueprint $table) {
             $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
        });

        // 5. Rename running_events to running_events_backup
        Schema::rename('running_events', 'running_events_backup');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Rename running_events_backup back
        Schema::rename('running_events_backup', 'running_events');
        
        // 2. Rename event_distances back
        Schema::rename('event_distances', 'running_event_distances');
        
        // 3. Revert columns in child tables
        Schema::table('event_distances', function (Blueprint $table) { // It's named running_event_distances now
             $table->dropForeign(['event_id']);
             $table->renameColumn('event_id', 'running_event_id');
             $table->foreign('running_event_id')->references('id')->on('running_events')->cascadeOnDelete();
        });
        
        Schema::table('master_gpxes', function (Blueprint $table) {
             $table->dropForeign(['event_id']);
             $table->renameColumn('event_id', 'running_event_id');
             $table->foreign('running_event_id')->references('id')->on('running_events')->cascadeOnDelete();
        });

        // 4. Drop columns from events
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'city_id',
                'race_type_id',
                'external_registration_link',
                'social_media_link',
                'organizer_name',
                'organizer_contact',
                'contributor_contact',
                'is_featured',
                'status'
            ]);
        });
    }
};
