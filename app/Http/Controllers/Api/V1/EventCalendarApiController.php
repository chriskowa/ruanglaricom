<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\RunningEventResource;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EventCalendarApiController extends BaseApiController
{
    /**
     * Browse upcoming race events calendar with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Event::query()
            ->where('is_active', true)
            ->with(['city', 'categories']);

        // Month filter (e.g., ?month=2026-08 or ?month=08)
        if ($request->filled('month')) {
            $monthInput = trim($request->month);
            try {
                if (preg_match('/^\d{4}-\d{2}$/', $monthInput)) {
                    $carbon = Carbon::parse($monthInput . '-01');
                    $query->whereYear('start_at', $carbon->year)
                        ->whereMonth('start_at', $carbon->month);
                } elseif (is_numeric($monthInput)) {
                    $query->whereMonth('start_at', (int) $monthInput);
                }
            } catch (\Exception $e) {
                // Ignore invalid month string
            }
        }

        // City filter
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        } elseif ($request->filled('city')) {
            $citySearch = trim($request->city);
            $query->where(function ($q) use ($citySearch) {
                $q->whereHas('city', function ($cq) use ($citySearch) {
                    $cq->where('name', 'like', "%{$citySearch}%");
                })->orWhere('location_name', 'like', "%{$citySearch}%");
            });
        }

        // Category filter (5k, 10k, hm, fm, trail, etc.)
        if ($request->filled('category')) {
            $catSearch = trim($request->category);
            $query->whereHas('categories', function ($cq) use ($catSearch) {
                $cq->where('name', 'like', "%{$catSearch}%");
            });
        }

        // Name search
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        // Featured filter
        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        // Order by start_at ascending (upcoming first)
        $events = $query->orderByRaw('COALESCE(start_at, created_at) ASC')
            ->paginate($request->get('per_page', 15));

        return $this->successResponse([
            'events' => RunningEventResource::collection($events),
            'pagination' => [
                'total' => $events->total(),
                'per_page' => $events->perPage(),
                'current_page' => $events->currentPage(),
                'last_page' => $events->lastPage(),
            ],
        ], 'Jadwal event lari berhasil dimuat');
    }

    /**
     * Get race event details by slug or ID
     */
    public function show(string $slug): JsonResponse
    {
        $event = Event::where('slug', $slug)
            ->orWhere('id', $slug)
            ->with(['city', 'categories'])
            ->first();

        if (! $event) {
            return $this->errorResponse('Event lari tidak ditemukan.', 404);
        }

        return $this->successResponse(new RunningEventResource($event), 'Detail event lari berhasil dimuat');
    }

    /**
     * Bookmark / Save race event for runner
     */
    public function toggleBookmark(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $event = Event::find($id);
        if (! $event) {
            return $this->errorResponse('Event lari tidak ditemukan.', 404);
        }

        $audit = $user->audit_history ?? [];
        $savedEventIds = $audit['saved_event_ids'] ?? [];

        if (in_array($id, $savedEventIds, true)) {
            $savedEventIds = array_values(array_diff($savedEventIds, [$id]));
            $isSaved = false;
        } else {
            $savedEventIds[] = $id;
            $isSaved = true;
        }

        $audit['saved_event_ids'] = array_values(array_unique($savedEventIds));
        $user->update(['audit_history' => $audit]);

        return $this->successResponse([
            'event_id' => $id,
            'is_saved' => $isSaved,
        ], $isSaved ? 'Event lari disimpan ke kalender' : 'Event lari dihapus dari simpanan');
    }

    /**
     * Get runner's saved race events
     */
    public function savedEvents(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $audit = $user->audit_history ?? [];
        $savedIds = $audit['saved_event_ids'] ?? [];

        if (empty($savedIds)) {
            return $this->successResponse([], 'Belum ada event lari yang disimpan');
        }

        $events = Event::whereIn('id', $savedIds)
            ->with(['city', 'categories'])
            ->orderByRaw('COALESCE(start_at, created_at) ASC')
            ->get();

        return $this->successResponse(RunningEventResource::collection($events), 'Daftar event lari simpanan berhasil dimuat');
    }
}
