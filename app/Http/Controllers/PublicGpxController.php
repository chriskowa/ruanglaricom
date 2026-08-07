<?php

namespace App\Http\Controllers;

use App\Models\MasterGpx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PublicGpxController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterGpx::query()
            ->with(['user', 'event'])
            ->where('is_published', true);

        // Filter: Search Keyword
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter: City
        if ($request->filled('city')) {
            $query->where('city', $request->input('city'));
        }

        // Filter: Distance Range
        if ($request->filled('distance')) {
            switch ($request->input('distance')) {
                case 'under_5k':
                    $query->where('distance_km', '<', 5);
                    break;
                case '5k_10k':
                    $query->whereBetween('distance_km', [5, 10]);
                    break;
                case '10k_21k':
                    $query->whereBetween('distance_km', [10, 21.1]);
                    break;
                case 'over_21k':
                    $query->where('distance_km', '>', 21.1);
                    break;
            }
        }

        // Filter: Elevation Gain
        if ($request->filled('elevation')) {
            switch ($request->input('elevation')) {
                case 'flat':
                    $query->where('elevation_gain_m', '<', 100);
                    break;
                case 'hilly':
                    $query->whereBetween('elevation_gain_m', [100, 300]);
                    break;
                case 'mountainous':
                    $query->where('elevation_gain_m', '>', 300);
                    break;
            }
        }

        // Sort
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'distance_desc':
                $query->orderByDesc('distance_km');
                break;
            case 'distance_asc':
                $query->orderBy('distance_km');
                break;
            case 'elevation_desc':
                $query->orderByDesc('elevation_gain_m');
                break;
            case 'oldest':
                $query->orderBy('created_at');
                break;
            case 'latest':
            default:
                $query->orderByDesc('created_at');
                break;
        }

        $items = $query->paginate(12)->withQueryString();

        // Get distinct cities for filter dropdown
        $cities = MasterGpx::query()
            ->where('is_published', true)
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->distinct()
            ->orderBy('city')
            ->pluck('city');

        return view('gpx.index', [
            'items' => $items,
            'cities' => $cities,
            'filters' => $request->all(),
        ]);
    }

    public function download(MasterGpx $masterGpx)
    {
        if (! $masterGpx->is_published && (! auth()->check() || (! auth()->user()->isAdmin() && auth()->id() !== $masterGpx->user_id))) {
            abort(404);
        }

        if (! $masterGpx->gpx_path || ! Storage::disk('public')->exists($masterGpx->gpx_path)) {
            abort(404, 'File GPX tidak ditemukan.');
        }

        $fileName = \Illuminate\Support\Str::slug($masterGpx->title) . '.gpx';
        return Storage::disk('public')->download($masterGpx->gpx_path, $fileName);
    }

    public function submitModal(Request $request)
    {
        if (! auth()->check()) {
            return response()->json([
                'success' => false,
                'requires_login' => true,
                'message' => 'Silakan login / register sebagai Runner terlebih dahulu untuk mengirimkan GPX rute.',
            ], 401);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'gpx_file' => 'required|file|mimes:gpx,xml,application/gpx+xml,text/xml|max:10240',
            'notes' => 'nullable|string|max:3000',
            'client_distance_km' => 'nullable|numeric',
            'client_elevation_gain' => 'nullable|numeric',
            'client_elevation_loss' => 'nullable|numeric',
            'coordinates_json' => 'nullable|string',
        ]);

        $path = $request->file('gpx_file')->store('master-gpx', 'public');
        $fullPath = Storage::disk('public')->path($path);

        $stats = $this->extractGpxStats($fullPath);

        $distanceKm = $stats['distance_km'] ?? $request->input('client_distance_km');
        $gainM = $stats['elevation_gain_m'] ?? $request->input('client_elevation_gain');
        $lossM = $stats['elevation_loss_m'] ?? $request->input('client_elevation_loss');

        $coords = null;
        if ($request->filled('coordinates_json')) {
            $coords = json_decode($request->input('coordinates_json'), true);
        }

        $user = auth()->user();

        $masterGpx = MasterGpx::create([
            'user_id' => $user->id,
            'title' => $request->input('title'),
            'city' => $request->input('city'),
            'gpx_path' => $path,
            'distance_km' => $distanceKm,
            'elevation_gain_m' => $gainM,
            'elevation_loss_m' => $lossM,
            'coordinates_json' => $coords,
            'is_published' => false, // Default unpublished as requested
            'notes' => $request->input('notes'),
        ]);

        // Award +10 run points to user
        $rewardPoints = 10;
        $user->addPoints($rewardPoints);

        return response()->json([
            'success' => true,
            'message' => 'Rute GPX berhasil dikirim! Status saat ini belum dipublish dan menunggu peninjauan admin. Anda mendapatkan +' . $rewardPoints . ' poin runner!',
            'gpx_id' => $masterGpx->id,
            'points_earned' => $rewardPoints,
            'total_points' => $user->fresh()->run_points,
        ]);
    }

    private function extractGpxStats(string $absolutePath): array
    {
        $distanceKm = null;
        $gain = null;
        $loss = null;

        $xml = @simplexml_load_file($absolutePath);
        if (! $xml) {
            return [
                'distance_km' => $distanceKm,
                'elevation_gain_m' => $gain,
                'elevation_loss_m' => $loss,
            ];
        }

        $trkpts = $xml->xpath('//*[local-name()="trkpt"]');
        if (! $trkpts || count($trkpts) < 2) {
            $trkpts = $xml->xpath('//*[local-name()="rtept"]');
        }
        if (! $trkpts || count($trkpts) < 2) {
            return [
                'distance_km' => $distanceKm,
                'elevation_gain_m' => $gain,
                'elevation_loss_m' => $loss,
            ];
        }

        $prevLat = null;
        $prevLon = null;
        $prevEle = null;
        $total = 0.0;
        $gainSum = 0.0;
        $lossSum = 0.0;
        $hasEle = false;

        foreach ($trkpts as $pt) {
            $lat = isset($pt['lat']) ? (float) $pt['lat'] : null;
            $lon = isset($pt['lon']) ? (float) $pt['lon'] : null;
            if (! is_finite($lat) || ! is_finite($lon)) {
                continue;
            }

            $eleNode = $pt->xpath('./*[local-name()="ele"]');
            $ele = null;
            if ($eleNode && isset($eleNode[0])) {
                $ele = (float) $eleNode[0];
                $hasEle = true;
            }

            if ($prevLat !== null) {
                $total += $this->haversineKm($prevLat, $prevLon, $lat, $lon);
                if ($hasEle && $prevEle !== null && $ele !== null) {
                    $d = $ele - $prevEle;
                    if ($d > 0) {
                        $gainSum += $d;
                    }
                    if ($d < 0) {
                        $lossSum += abs($d);
                    }
                }
            }

            $prevLat = $lat;
            $prevLon = $lon;
            $prevEle = $ele;
        }

        $distanceKm = $total > 0 ? round($total, 3) : null;
        if ($hasEle) {
            $gain = (int) round($gainSum);
            $loss = (int) round($lossSum);
        }

        return [
            'distance_km' => $distanceKm,
            'elevation_gain_m' => $gain,
            'elevation_loss_m' => $loss,
        ];
    }

    private function haversineKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $R = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }
}
