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
            if (! Schema::hasColumn('master_gpxes', 'route_type')) {
                $table->string('route_type', 30)->default('road')->after('city');
                $table->index('route_type');
            }
        });

        // Backfill existing records with smart detection
        $records = DB::table('master_gpxes')
            ->select('id', 'title', 'notes', 'description', 'distance_km', 'elevation_gain_m')
            ->get();

        $trailKeywords = ['trail', 'gunung', 'bukit', 'summit', 'ridge', 'forest', 'tahura', 'rinjani', 'merbabu', 'bromo', 'sikunir', 'lawu', 'ciremai', 'semeru', 'patuha', 'kawah', 'curug', 'alas'];

        foreach ($records as $record) {
            $text = strtolower(($record->title ?? '') . ' ' . ($record->notes ?? '') . ' ' . ($record->description ?? ''));
            $isTrail = false;

            foreach ($trailKeywords as $kw) {
                if (str_contains($text, $kw)) {
                    $isTrail = true;
                    break;
                }
            }

            if (! $isTrail && ! empty($record->distance_km) && (float)$record->distance_km > 0 && ! empty($record->elevation_gain_m)) {
                $gainPerKm = (float)$record->elevation_gain_m / (float)$record->distance_km;
                // Trail running typically has steep elevation density > 35m gain/km
                if ($gainPerKm >= 35.0) {
                    $isTrail = true;
                }
            }

            $type = $isTrail ? 'trail' : 'road';

            DB::table('master_gpxes')->where('id', $record->id)->update([
                'route_type' => $type,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('master_gpxes', function (Blueprint $table) {
            if (Schema::hasColumn('master_gpxes', 'route_type')) {
                $table->dropIndex(['route_type']);
                $table->dropColumn('route_type');
            }
        });
    }
};
