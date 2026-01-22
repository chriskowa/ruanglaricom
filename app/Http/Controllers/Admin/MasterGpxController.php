<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\MasterGpx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MasterGpxController extends Controller
{
    public function index()
    {
        $items = MasterGpx::query()
            ->with(['runningEvent'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.master-gpx.index', [
            'withSidebar' => true,
            'items' => $items,
        ]);
    }

    public function create()
    {
        $events = RunningEvent::query()
            ->orderByDesc('event_date')
            ->limit(1000)
            ->get();

        return view('admin.master-gpx.create', [
            'withSidebar' => true,
            'events' => $events,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'running_event_id' => 'nullable|exists:running_events,id',
            'title' => 'required|string|max:255',
            'gpx_file' => 'required|file|mimes:gpx,xml,application/gpx+xml,text/xml|max:10240',
            'is_published' => 'nullable|boolean',
            'notes' => 'nullable|string|max:5000',
        ]);

        $path = $request->file('gpx_file')->store('master-gpx', 'public');

        $stats = $this->extractGpxStats(Storage::disk('public')->path($path));

        $item = MasterGpx::create([
            'running_event_id' => $data['running_event_id'] ?? null,
            'title' => $data['title'],
            'gpx_path' => $path,
            'distance_km' => $stats['distance_km'],
            'elevation_gain_m' => $stats['elevation_gain_m'],
            'elevation_loss_m' => $stats['elevation_loss_m'],
            'is_published' => $request->boolean('is_published'),
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('admin.master-gpx.edit', $item)->with('success', 'Master GPX berhasil dibuat.');
    }

    public function edit(MasterGpx $masterGpx)
    {
        $events = RunningEvent::query()
            ->orderByDesc('event_date')
            ->limit(1000)
            ->get();

        return view('admin.master-gpx.edit', [
            'withSidebar' => true,
            'item' => $masterGpx->load('runningEvent'),
            'events' => $events,
        ]);
    }

    public function update(Request $request, MasterGpx $masterGpx)
    {
        $data = $request->validate([
            'running_event_id' => 'nullable|exists:running_events,id',
            'title' => 'required|string|max:255',
            'gpx_file' => 'nullable|file|mimes:gpx,xml,application/gpx+xml,text/xml|max:10240',
            'is_published' => 'nullable|boolean',
            'notes' => 'nullable|string|max:5000',
        ]);

        $update = [
            'running_event_id' => $data['running_event_id'] ?? null,
            'title' => $data['title'],
            'is_published' => $request->boolean('is_published'),
            'notes' => $data['notes'] ?? null,
        ];

        if ($request->hasFile('gpx_file')) {
            $oldPath = $masterGpx->gpx_path;
            $path = $request->file('gpx_file')->store('master-gpx', 'public');
            $stats = $this->extractGpxStats(Storage::disk('public')->path($path));

            $update['gpx_path'] = $path;
            $update['distance_km'] = $stats['distance_km'];
            $update['elevation_gain_m'] = $stats['elevation_gain_m'];
            $update['elevation_loss_m'] = $stats['elevation_loss_m'];

            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        $masterGpx->update($update);

        return redirect()->route('admin.master-gpx.edit', $masterGpx)->with('success', 'Master GPX berhasil diupdate.');
    }

    public function destroy(MasterGpx $masterGpx)
    {
        $masterGpx->delete();

        return redirect()->route('admin.master-gpx.index')->with('success', 'Master GPX berhasil dihapus.');
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
            if (! is_finite($lat) || ! is_finite($lon)) continue;

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
                    if ($d > 0) $gainSum += $d;
                    if ($d < 0) $lossSum += abs($d);
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
        $a =
            sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}

