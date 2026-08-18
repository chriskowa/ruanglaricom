<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\MasterGpx;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MasterGpxController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterGpx::query()
            ->with(['event', 'user'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            if ($request->input('status') === 'published') {
                $query->where('is_published', true);
            } elseif ($request->input('status') === 'draft') {
                $query->where('is_published', false);
            }
        }

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        $items = $query->paginate(25)->withQueryString();

        return view('admin.master-gpx.index', [
            'withSidebar' => true,
            'items' => $items,
            'status' => $request->input('status'),
            'search' => $request->input('q'),
        ]);
    }

    public function create()
    {
        $events = Event::directory()
            ->orderByDesc('start_at')
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
            'event_id' => ['nullable', Rule::exists('events', 'id')->where('event_kind', 'directory')],
            'title' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'gpx_file' => 'required|file|mimes:gpx,xml,application/gpx+xml,text/xml|max:10240',
            'is_published' => 'nullable|boolean',
            'notes' => 'nullable|string|max:5000',
        ]);

        $path = $request->file('gpx_file')->store('master-gpx', 'public');

        $stats = $this->extractGpxStats(Storage::disk('public')->path($path));

        $item = MasterGpx::create([
            'event_id' => $data['event_id'] ?? null,
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'city' => $data['city'] ?? null,
            'description' => $data['description'] ?? null,
            'gpx_path' => $path,
            'distance_km' => $stats['distance_km'],
            'elevation_gain_m' => $stats['elevation_gain_m'],
            'elevation_loss_m' => $stats['elevation_loss_m'],
            'is_published' => $request->boolean('is_published'),
            'notes' => $data['notes'] ?? null,
        ]);

        // Send notification to admins
        try {
            $adminUsers = \App\Models\User::where('role', 'admin')->get();
            $now = now();
            $notifRows = [];
            foreach ($adminUsers as $admin) {
                $notifRows[] = [
                    'user_id' => $admin->id,
                    'type' => 'gpx_submission',
                    'title' => 'Master GPX Baru Ditambahkan',
                    'message' => 'Admin ' . (auth()->user()->name ?? '') . ' menambahkan rute Master GPX baru: "' . $item->title . '" (' . ($item->distance_km ? number_format((float) $item->distance_km, 1) . ' km, ' : '') . ($item->city ?? 'Indonesia') . ').',
                    'reference_type' => 'MasterGpx',
                    'reference_id' => $item->id,
                    'is_read' => false,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            if (!empty($notifRows)) {
                \App\Models\Notification::insert($notifRows);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create admin notification for Master GPX creation: ' . $e->getMessage());
        }

        return redirect()->route('admin.master-gpx.edit', $item)->with('success', 'Master GPX berhasil dibuat.');
    }

    public function edit(MasterGpx $masterGpx)
    {
        $events = Event::directory()
            ->orderByDesc('start_at')
            ->limit(1000)
            ->get();

        return view('admin.master-gpx.edit', [
            'withSidebar' => true,
            'item' => $masterGpx->load(['event', 'user']),
            'events' => $events,
        ]);
    }

    public function update(Request $request, MasterGpx $masterGpx)
    {
        $data = $request->validate([
            'event_id' => ['nullable', Rule::exists('events', 'id')->where('event_kind', 'directory')],
            'title' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'gpx_file' => 'nullable|file|mimes:gpx,xml,application/gpx+xml,text/xml|max:10240',
            'is_published' => 'nullable|boolean',
            'notes' => 'nullable|string|max:5000',
        ]);

        $wasPublished = (bool) $masterGpx->is_published;

        $update = [
            'event_id' => $data['event_id'] ?? null,
            'title' => $data['title'],
            'city' => $data['city'] ?? null,
            'description' => $data['description'] ?? null,
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

        // Notify submitter if published status changed from false to true
        if (!$wasPublished && $masterGpx->is_published && $masterGpx->user_id) {
            try {
                \App\Models\Notification::create([
                    'user_id' => $masterGpx->user_id,
                    'type' => 'gpx_published',
                    'title' => 'Rute GPX Disetujui & Tayang!',
                    'message' => 'Kabar gembira! Rute GPX "' . $masterGpx->title . '" (' . ($masterGpx->distance_km ? number_format((float) $masterGpx->distance_km, 1) . ' km, ' : '') . ($masterGpx->city ?? 'Indonesia') . ') yang Anda kirimkan telah disetujui admin dan kini tayang di Katalog Rute RuangLari.',
                    'reference_type' => 'MasterGpx',
                    'reference_id' => $masterGpx->id,
                    'is_read' => false,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to notify user on GPX publish update: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.master-gpx.edit', $masterGpx)->with('success', 'Master GPX berhasil diupdate.');
    }

    public function togglePublish(MasterGpx $masterGpx)
    {
        $newStatus = ! $masterGpx->is_published;
        $masterGpx->update([
            'is_published' => $newStatus,
        ]);

        // Notify submitter if published status became true
        if ($newStatus && $masterGpx->user_id) {
            try {
                \App\Models\Notification::create([
                    'user_id' => $masterGpx->user_id,
                    'type' => 'gpx_published',
                    'title' => 'Rute GPX Disetujui & Tayang!',
                    'message' => 'Kabar gembira! Rute GPX "' . $masterGpx->title . '" (' . ($masterGpx->distance_km ? number_format((float) $masterGpx->distance_km, 1) . ' km, ' : '') . ($masterGpx->city ?? 'Indonesia') . ') yang Anda kirimkan telah disetujui admin dan kini tayang di Katalog Rute RuangLari.',
                    'reference_type' => 'MasterGpx',
                    'reference_id' => $masterGpx->id,
                    'is_read' => false,
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed to notify user on GPX togglePublish: ' . $e->getMessage());
            }
        }

        $statusText = $masterGpx->is_published ? 'dipublish' : 'dijadikan draft (belum publish)';

        return back()->with('success', "Master GPX '{$masterGpx->title}' berhasil {$statusText}.");
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
        $a =
            sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }
}
