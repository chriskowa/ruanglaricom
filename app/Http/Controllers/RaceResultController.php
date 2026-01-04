<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\RaceResult;
use Illuminate\Http\Request;

class RaceResultController extends Controller
{
    /**
     * Get race results for public API
     */
    public function index(Request $request, $slug)
    {
        // Decode slug if it contains encoded characters
        $slug = urldecode($slug);

        try {
            $event = Event::where('slug', $slug)->firstOrFail();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
                'slug' => $slug,
                'data' => [],
                'total' => 0,
            ], 404);
        }

        $query = RaceResult::forEvent($event->id)
            ->orderBy('rank_category', 'asc')
            ->orderBy('chip_time', 'asc');

        // Filter by category
        if ($request->has('category') && $request->category !== 'All') {
            $query->forCategory($request->category);
        }

        // Filter by gender
        if ($request->has('gender') && $request->gender !== 'All') {
            $query->forGender($request->gender);
        }

        // Search by name or BIB
        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('runner_name', 'like', "%{$search}%")
                    ->orWhere('bib_number', 'like', "%{$search}%");
            });
        }

        $results = $query->get();

        // Format data untuk frontend
        $formattedResults = $results->map(function ($result) {
            return [
                'rank' => $result->rank_category ?? $result->rank_overall ?? 0,
                'name' => $result->runner_name,
                'bib' => $result->bib_number,
                'category' => $result->category_code ?? 'N/A',
                'gender' => $result->gender,
                'nationality' => $result->nationality ?? 'IDN',
                'gunTime' => $result->getFormattedGunTime(),
                'chipTime' => $result->getFormattedChipTime(),
                'pace' => $result->pace ?? '--:--',
                'isPodium' => $result->is_podium ?? false,
                'podiumPosition' => $result->podium_position,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedResults->values()->all(), // Reset array keys
            'total' => $formattedResults->count(),
        ]);
    }
}
