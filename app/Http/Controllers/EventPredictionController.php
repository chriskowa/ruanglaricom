<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\PredictionErrorLog;
use App\Models\RaceCategory;
use App\Services\EventTimePredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EventPredictionController extends Controller
{
    public function show(string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();
        $categories = $event->categories()
            ->where('is_active', true)
            ->with('masterGpx')
            ->get();

        return view('events.prediction', [
            'event' => $event,
            'categories' => $categories,
        ]);
    }

    public function predict(Request $request, string $slug, EventTimePredictionService $service)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'category_id' => ['required', 'integer'],
            'weather' => ['required', 'in:panas,dingin,hujan,gerimis'],
            'pb_h' => ['required', 'integer', 'min:0', 'max:23'],
            'pb_m' => ['required', 'integer', 'min:0', 'max:59'],
            'pb_s' => ['required', 'integer', 'min:0', 'max:59'],
            'pb_date' => ['required', 'date', 'after_or_equal:'.now()->subMonths(3)->toDateString(), 'before_or_equal:'.now()->toDateString()],
        ]);

        $category = RaceCategory::query()
            ->where('event_id', $event->id)
            ->whereKey($validated['category_id'])
            ->with('masterGpx')
            ->firstOrFail();

        $pbSeconds = ((int) $validated['pb_h'] * 3600) + ((int) $validated['pb_m'] * 60) + (int) $validated['pb_s'];
        if ($pbSeconds <= 0) {
            return response()->json([
                'ok' => false,
                'message' => 'PB tidak valid.',
            ], 422);
        }

        [$minPb, $maxPb] = $this->pbBoundsSeconds((float) ($category->distance_km ?? 0));
        if ($minPb !== null && $pbSeconds < $minPb) {
            return response()->json([
                'ok' => false,
                'message' => 'PB terlalu cepat untuk jarak kategori ini.',
            ], 422);
        }
        if ($maxPb !== null && $pbSeconds > $maxPb) {
            return response()->json([
                'ok' => false,
                'message' => 'PB terlalu lambat untuk jarak kategori ini.',
            ], 422);
        }

        try {
            $result = $service->predict($category, $validated['weather'], $pbSeconds, new \DateTimeImmutable($validated['pb_date']));
        } catch (\Throwable $e) {
            PredictionErrorLog::create([
                'event_id' => $event->id,
                'race_category_id' => $category->id,
                'context' => [
                    'weather' => $validated['weather'],
                    'pb_seconds' => $pbSeconds,
                    'pb_date' => $validated['pb_date'],
                ],
                'error_message' => mb_substr((string) $e->getMessage(), 0, 2000),
            ]);

            Log::error('Prediction error', [
                'event_id' => $event->id,
                'race_category_id' => $category->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Terjadi error saat menghitung prediksi.',
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'slug' => $event->slug,
            ],
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'distance_km' => $category->distance_km,
                'master_gpx_id' => $category->master_gpx_id,
            ],
            'result' => $result,
        ]);
    }

    private function pbBoundsSeconds(float $distanceKm): array
    {
        if (abs($distanceKm - 5.0) < 0.6) {
            return [10 * 60, 90 * 60];
        }
        if (abs($distanceKm - 10.0) < 1.0) {
            return [20 * 60, 3 * 3600];
        }
        if (abs($distanceKm - 21.1) < 1.0 || abs($distanceKm - 21.0) < 1.0) {
            return [60 * 60, 6 * 3600];
        }
        if (abs($distanceKm - 42.2) < 2.0 || abs($distanceKm - 42.0) < 2.0) {
            return [2 * 3600, 9 * 3600];
        }

        return [null, null];
    }
}
