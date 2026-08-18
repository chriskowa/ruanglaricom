<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_gpxes', function (Blueprint $table) {
            if (! Schema::hasColumn('master_gpxes', 'start_latitude')) {
                $table->decimal('start_latitude', 10, 7)->nullable()->after('coordinates_json');
            }
            if (! Schema::hasColumn('master_gpxes', 'start_longitude')) {
                $table->decimal('start_longitude', 10, 7)->nullable()->after('start_latitude');
            }
            $table->index(['start_latitude', 'start_longitude']);
        });

        // Backfill start_latitude and start_longitude from coordinates_json
        $records = DB::table('master_gpxes')
            ->whereNotNull('coordinates_json')
            ->select('id', 'coordinates_json')
            ->get();

        foreach ($records as $record) {
            if (empty($record->coordinates_json)) {
                continue;
            }

            $coords = is_array($record->coordinates_json)
                ? $record->coordinates_json
                : json_decode($record->coordinates_json, true);

            if (is_array($coords) && ! empty($coords)) {
                $firstPoint = $coords[0];
                $lat = null;
                $lng = null;

                if (is_array($firstPoint)) {
                    if (isset($firstPoint['lat']) && isset($firstPoint['lng'])) {
                        $lat = $firstPoint['lat'];
                        $lng = $firstPoint['lng'];
                    } elseif (isset($firstPoint[0]) && isset($firstPoint[1])) {
                        $lat = $firstPoint[0];
                        $lng = $firstPoint[1];
                    }
                } elseif (is_object($firstPoint)) {
                    if (isset($firstPoint->lat) && isset($firstPoint->lng)) {
                        $lat = $firstPoint->lat;
                        $lng = $firstPoint->lng;
                    }
                }

                if ($lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng)) {
                    DB::table('master_gpxes')->where('id', $record->id)->update([
                        'start_latitude' => round((float) $lat, 7),
                        'start_longitude' => round((float) $lng, 7),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('master_gpxes', function (Blueprint $table) {
            if (Schema::hasColumn('master_gpxes', 'start_latitude')) {
                $table->dropIndex(['start_latitude', 'start_longitude']);
                $table->dropColumn(['start_latitude', 'start_longitude']);
            }
        });
    }
};
