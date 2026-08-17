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

    public function show($identifier)
    {
        $item = MasterGpx::query()
            ->with(['user', 'event'])
            ->where(function ($q) use ($identifier) {
                if (is_numeric($identifier)) {
                    $q->where('id', $identifier)->orWhere('slug', $identifier);
                } else {
                    $q->where('slug', $identifier);
                }
            })
            ->firstOrFail();

        if (! $item->is_published && (! auth()->check() || (! auth()->user()->isAdmin() && auth()->id() !== $item->user_id))) {
            abort(404);
        }

        // Auto-generate slug if missing
        if (empty($item->slug)) {
            $item->slug = MasterGpx::generateUniqueSlug($item->title, $item->id);
            $item->save();
        }

        // Auto-parse coordinates_json from GPX file if missing or lacks elevation data
        if ($item->gpx_path && Storage::disk('public')->exists($item->gpx_path)) {
            $needsParse = empty($item->coordinates_json) ||
                (is_array($item->coordinates_json) && count($item->coordinates_json) > 0 && !array_key_exists('ele', (array)$item->coordinates_json[0]));
            
            if ($needsParse) {
                $parsedCoords = $this->parseGpxCoordinates(Storage::disk('public')->path($item->gpx_path));
                if (! empty($parsedCoords)) {
                    $item->coordinates_json = $parsedCoords;
                    $item->save();
                }
            }
        }

        // Related GPX routes in the same city or general
        $related = MasterGpx::query()
            ->where('is_published', true)
            ->where('id', '!=', $item->id)
            ->where(function ($q) use ($item) {
                if ($item->city) {
                    $q->where('city', $item->city);
                }
            })
            ->limit(4)
            ->get();

        if ($related->count() < 4) {
            $existingIds = $related->pluck('id')->push($item->id);
            $fallback = MasterGpx::query()
                ->where('is_published', true)
                ->whereNotIn('id', $existingIds)
                ->orderByDesc('created_at')
                ->limit(4 - $related->count())
                ->get();
            $related = $related->concat($fallback);
        }

        return view('gpx.show', [
            'item' => $item,
            'related' => $related,
        ]);
    }

    public function publishedJson(Request $request)
    {
        $query = MasterGpx::query()
            ->with('user:id,name')
            ->where('is_published', true);

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $items = $query->orderByDesc('created_at')->limit(50)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'title' => $item->title,
                'slug' => $item->slug ?: $item->id,
                'city' => $item->city ?? 'Indonesia',
                'distance_km' => $item->distance_km ? (float) $item->distance_km : null,
                'elevation_gain_m' => $item->elevation_gain_m,
                'uploader' => $item->user?->name ?? 'RuangLari',
                'coordinates_json' => $item->coordinates_json,
                'gpx_url' => $item->gpx_path ? Storage::disk('public')->url($item->gpx_path) : null,
                'detail_url' => route('gpx.show', $item->slug ?: $item->id),
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    public function download($identifier)
    {
        $masterGpx = MasterGpx::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->firstOrFail();

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
            'description' => 'nullable|string|max:5000',
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
            'description' => $request->input('description') ?? $request->input('notes'),
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

    private function parseGpxCoordinates(string $absolutePath): array
    {
        $xml = @simplexml_load_file($absolutePath);
        if (! $xml) {
            return [];
        }

        $trkpts = $xml->xpath('//*[local-name()="trkpt"]');
        if (! $trkpts || count($trkpts) < 2) {
            $trkpts = $xml->xpath('//*[local-name()="rtept"]');
        }
        if (! $trkpts) {
            return [];
        }

        $coords = [];
        $totalPts = count($trkpts);
        $step = $totalPts > 2000 ? (int) ceil($totalPts / 2000) : 1;
        $cumulativeDist = 0.0;
        $prevLat = null;
        $prevLon = null;

        for ($i = 0; $i < $totalPts; $i += $step) {
            $pt = $trkpts[$i];
            $lat = isset($pt['lat']) ? (float) $pt['lat'] : null;
            $lon = isset($pt['lon']) ? (float) $pt['lon'] : (isset($pt['lng']) ? (float) $pt['lng'] : null);

            $eleNode = $pt->xpath('./*[local-name()="ele"]');
            $ele = null;
            if ($eleNode && isset($eleNode[0])) {
                $ele = round((float) $eleNode[0], 1);
            }

            if (is_finite($lat) && is_finite($lon)) {
                if ($prevLat !== null && $prevLon !== null) {
                    $cumulativeDist += $this->haversineKm($prevLat, $prevLon, $lat, $lon);
                }
                $prevLat = $lat;
                $prevLon = $lon;

                $coords[] = [
                    'lat' => $lat,
                    'lng' => $lon,
                    'ele' => $ele,
                    'dist' => round($cumulativeDist, 3),
                ];
            }
        }

        return $coords;
    }

    /**
     * Display list of GPX routes uploaded by the logged-in runner
     */
    public function myGpx(Request $request)
    {
        $query = MasterGpx::query()
            ->where('user_id', auth()->id())
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(15)->withQueryString();

        return view('runner.gpx.index', [
            'withSidebar' => true,
            'items' => $items,
            'search' => $request->input('q'),
        ]);
    }

    /**
     * Edit form for runner's own GPX route
     */
    public function editMyGpx(MasterGpx $masterGpx)
    {
        if ((int) $masterGpx->user_id !== (int) auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit rute GPX ini.');
        }

        return view('runner.gpx.edit', [
            'withSidebar' => true,
            'item' => $masterGpx,
        ]);
    }

    /**
     * Update runner's own GPX route
     */
    public function updateMyGpx(Request $request, MasterGpx $masterGpx)
    {
        if ((int) $masterGpx->user_id !== (int) auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah rute GPX ini.');
        }

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:3000',
        ]);

        $masterGpx->update([
            'title' => $data['title'],
            'city' => $data['city'] ?? null,
            'description' => $data['description'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('runner.gpx.index')->with('success', 'Rute GPX berhasil diperbarui.');
    }

    /**
     * Delete runner's own GPX route
     */
    public function destroyMyGpx(MasterGpx $masterGpx)
    {
        if ((int) $masterGpx->user_id !== (int) auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus rute GPX ini.');
        }

        if ($masterGpx->gpx_path) {
            Storage::disk('public')->delete($masterGpx->gpx_path);
        }

        $masterGpx->delete();

        return redirect()->route('runner.gpx.index')->with('success', 'Rute GPX berhasil dihapus.');
    }
}
