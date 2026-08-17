<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserActivityController extends Controller
{
    /**
     * Free Run Live GPS Tracker (/run) - Strava-like Live GPS Running.
     */
    public function freeRun()
    {
        return view('run.index');
    }

    /**
     * Store a recorded running activity from live navigation / GPS session.
     */
    public function store(Request $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan login terlebih dahulu untuk menyimpan aktivitas lari Anda.',
                'require_login' => true,
            ], 401);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'master_gpx_id' => 'nullable|exists:master_gpxes,id',
            'sport_type' => 'nullable|string|max:50',
            'distance_km' => 'required|numeric|min:0.01',
            'moving_time_s' => 'required|integer|min:1',
            'elapsed_time_s' => 'nullable|integer',
            'avg_pace_sec' => 'nullable|integer',
            'max_pace_sec' => 'nullable|integer',
            'elevation_gain_m' => 'nullable|numeric',
            'elevation_loss_m' => 'nullable|numeric',
            'calories' => 'nullable|integer',
            'coordinates_json' => 'nullable',
            'splits_json' => 'nullable',
            'notes' => 'nullable|string|max:2000',
            'is_public' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $dist = (float) $validated['distance_km'];
        $movingSec = (int) $validated['moving_time_s'];
        $elapsedSec = !empty($validated['elapsed_time_s']) ? (int) $validated['elapsed_time_s'] : $movingSec;

        // Calculate avg pace if not provided
        $avgPaceSec = !empty($validated['avg_pace_sec']) ? (int) $validated['avg_pace_sec'] : ($dist > 0 ? (int) round($movingSec / $dist) : 0);

        // Estimate calories if not provided (~60 kcal per km for average runner)
        $calories = !empty($validated['calories']) ? (int) $validated['calories'] : (int) round($dist * 62);

        // Parse coordinates and splits if passed as string
        $coords = is_string($request->input('coordinates_json')) ? json_decode($request->input('coordinates_json'), true) : $request->input('coordinates_json');
        $splits = is_string($request->input('splits_json')) ? json_decode($request->input('splits_json'), true) : $request->input('splits_json');

        $now = Carbon::now();
        $startTime = $now->copy()->subSeconds($elapsedSec);

        $activity = UserActivity::create([
            'user_id' => $user->id,
            'master_gpx_id' => $validated['master_gpx_id'] ?? null,
            'title' => $validated['title'],
            'sport_type' => $validated['sport_type'] ?? 'running',
            'start_time' => $startTime,
            'end_time' => $now,
            'distance_km' => $dist,
            'moving_time_s' => $movingSec,
            'elapsed_time_s' => $elapsedSec,
            'avg_pace_sec' => $avgPaceSec,
            'max_pace_sec' => $validated['max_pace_sec'] ?? null,
            'avg_speed_kmh' => $movingSec > 0 ? round(($dist / ($movingSec / 3600)), 2) : 0,
            'elevation_gain_m' => (float) ($validated['elevation_gain_m'] ?? 0),
            'elevation_loss_m' => (float) ($validated['elevation_loss_m'] ?? 0),
            'calories' => $calories,
            'coordinates_json' => $coords,
            'splits_json' => $splits,
            'notes' => $validated['notes'] ?? null,
            'is_public' => $request->boolean('is_public', true),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas lari berhasil disimpan!',
            'activity_id' => $activity->id,
            'redirect_url' => route('activities.show', $activity->id),
        ]);
    }

    /**
     * Show activity detail page (Strava-like running activity).
     */
    public function show(int $id)
    {
        $activity = UserActivity::with(['user', 'masterGpx'])->findOrFail($id);

        if (!$activity->is_public && (!Auth::check() || Auth::id() !== $activity->user_id)) {
            abort(403, 'Aktivitas ini bersifat privat.');
        }

        return view('activities.show', compact('activity'));
    }

    /**
     * Download recorded activity track as a standard GPX file.
     */
    public function downloadGpx(int $id): Response
    {
        $activity = UserActivity::findOrFail($id);

        $coords = $activity->coordinates_json ?? [];
        $title = $activity->title ?: 'RuangLari Activity';
        $safeFilename = Str::slug($title) . '_' . ($activity->start_time ? $activity->start_time->format('Ymd_His') : date('Ymd')) . '.gpx';

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<gpx version="1.1" creator="RuangLari.com" xmlns="http://www.topografix.com/GPX/1/1">' . "\n";
        $xml .= '  <metadata>' . "\n";
        $xml .= '    <name>' . htmlspecialchars($title, ENT_XML1) . '</name>' . "\n";
        $xml .= '    <time>' . ($activity->start_time ? $activity->start_time->toIso8601String() : now()->toIso8601String()) . '</time>' . "\n";
        $xml .= '  </metadata>' . "\n";
        $xml .= '  <trk>' . "\n";
        $xml .= '    <name>' . htmlspecialchars($title, ENT_XML1) . '</name>' . "\n";
        $xml .= '    <type>running</type>' . "\n";
        $xml .= '    <trkseg>' . "\n";

        if (is_array($coords)) {
            foreach ($coords as $pt) {
                $lat = $pt['lat'] ?? (is_array($pt) ? ($pt[0] ?? null) : null);
                $lng = $pt['lng'] ?? (is_array($pt) ? ($pt[1] ?? null) : null);
                $ele = $pt['ele'] ?? (is_array($pt) && isset($pt[2]) ? $pt[2] : null);
                $time = isset($pt['time']) ? date('c', (int) ($pt['time'] / 1000)) : null;

                if ($lat !== null && $lng !== null) {
                    $xml .= '      <trkpt lat="' . $lat . '" lon="' . $lng . '">';
                    if ($ele !== null) {
                        $xml .= '<ele>' . round((float) $ele, 1) . '</ele>';
                    }
                    if ($time) {
                        $xml .= '<time>' . $time . '</time>';
                    }
                    $xml .= '</trkpt>' . "\n";
                }
            }
        }

        $xml .= '    </trkseg>' . "\n";
        $xml .= '  </trk>' . "\n";
        $xml .= '</gpx>';

        return response($xml, 200, [
            'Content-Type' => 'application/gpx+xml',
            'Content-Disposition' => 'attachment; filename="' . $safeFilename . '"',
        ]);
    }

    /**
     * Delete user activity.
     */
    public function destroy(int $id): JsonResponse
    {
        $activity = UserActivity::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $activity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas berhasil dihapus.',
        ]);
    }
}
