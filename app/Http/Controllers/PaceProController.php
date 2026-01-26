<?php

namespace App\Http\Controllers;

use App\Models\MasterGpx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaceProController extends Controller
{
    public function index(Request $request)
    {
        $gpxFiles = MasterGpx::query()
            ->with(['event.city'])
            ->where('is_published', true)
            ->leftJoin('events', 'master_gpxes.event_id', '=', 'events.id')
            ->orderByDesc('events.start_at')
            ->orderByDesc('master_gpxes.created_at')
            ->select('master_gpxes.*')
            ->get()
            ->map(function (MasterGpx $gpx) {
                return [
                    'id' => $gpx->id,
                    'title' => $gpx->title,
                    'distance_km' => $gpx->distance_km,
                    'elevation_gain_m' => $gpx->elevation_gain_m,
                    'event_name' => $gpx->event?->name,
                    'event_date' => $gpx->event?->start_at?->format('Y-m-d'),
                    'event_city' => $gpx->event?->city?->name,
                    'download_url' => route('tools.pace-pro.gpx', ['masterGpx' => $gpx->id]),
                ];
            })
            ->values();

        return view('tools.pace-pro', [
            'gpxFiles' => $gpxFiles,
        ]);
    }

    public function gpx(MasterGpx $masterGpx)
    {
        abort_unless($masterGpx->is_published, 404);

        $disk = Storage::disk('public');
        abort_unless($masterGpx->gpx_path && $disk->exists($masterGpx->gpx_path), 404);

        $path = $disk->path($masterGpx->gpx_path);

        return response()->file($path, [
            'Content-Type' => 'application/gpx+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
