<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\CustomWorkout;
use App\Models\Event;
use App\Models\ProgramEnrollment;
use App\Models\ProgramSessionTracking;
use App\Models\StravaActivity;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class RunnerScheduleApiController extends BaseApiController
{
    /**
     * Get runner's monthly calendar events (programs + custom workouts + race events + completed runs)
     */
    public function month(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $monthInput = $request->get('month', now()->format('Y-m'));
        try {
            $startOfMonth = Carbon::parse($monthInput . '-01')->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth();
        } catch (\Exception $e) {
            $startOfMonth = now()->startOfMonth();
            $endOfMonth = now()->endOfMonth();
        }

        // 1. Fetch Program Enrollments & Sessions
        $enrollments = ProgramEnrollment::where('runner_id', $user->id)
            ->where('status', 'active')
            ->whereHas('program', fn ($q) => $q->where('is_active', true))
            ->with('program')
            ->get();

        $enrollmentIds = $enrollments->pluck('id')->toArray();
        $trackings = ProgramSessionTracking::whereIn('enrollment_id', $enrollmentIds)
            ->get()
            ->groupBy('enrollment_id');

        $calendarDays = [];

        foreach ($enrollments as $enrollment) {
            $program = $enrollment->program;
            $sessions = $program->program_json['sessions'] ?? [];

            if (! is_array($sessions) || empty($sessions)) {
                continue;
            }

            try {
                $startDate = Carbon::parse($enrollment->start_date);
            } catch (\Exception $e) {
                continue;
            }

            $enrollmentTrackings = $trackings->get($enrollment->id) ?? collect();

            foreach ($sessions as $session) {
                if (! isset($session['day']) || ! is_numeric($session['day'])) {
                    continue;
                }

                $day = (int) $session['day'];
                $sessionDate = $startDate->copy()->addDays($day - 1);

                $tracking = $enrollmentTrackings->firstWhere('session_day', $day);
                if ($tracking && $tracking->rescheduled_date) {
                    $sessionDate = Carbon::parse($tracking->rescheduled_date);
                }

                if ($sessionDate->between($startOfMonth, $endOfMonth)) {
                    $dateKey = $sessionDate->format('Y-m-d');
                    if (! isset($calendarDays[$dateKey])) {
                        $calendarDays[$dateKey] = [
                            'date' => $dateKey,
                            'formatted_date' => $sessionDate->translatedFormat('d M Y'),
                            'day_name' => $sessionDate->translatedFormat('l'),
                            'items' => [],
                        ];
                    }

                    $status = $tracking ? $tracking->status : 'pending';

                    $calendarDays[$dateKey]['items'][] = [
                        'id' => $tracking?->id ?: "prog_{$enrollment->id}_d{$day}",
                        'type' => 'program_session',
                        'program_name' => $program->title,
                        'session_name' => $session['name'] ?? 'Latihan Sesi ' . $day,
                        'activity_type' => $session['activity'] ?? 'run',
                        'distance_km' => $session['distance'] ?? null,
                        'duration_min' => $session['duration'] ?? null,
                        'target_pace' => $session['pace'] ?? null,
                        'description' => $session['description'] ?? null,
                        'status' => $status,
                        'completed_at' => optional($tracking?->completed_at)->toISOString(),
                        'rescheduled_date' => optional($tracking?->rescheduled_date)->format('Y-m-d'),
                    ];
                }
            }
        }

        // 2. Fetch Custom Workouts
        $customWorkouts = CustomWorkout::where('runner_id', $user->id)
            ->whereBetween('workout_date', [$startOfMonth->format('Y-m-d'), $endOfMonth->format('Y-m-d')])
            ->get();

        foreach ($customWorkouts as $cw) {
            $dateKey = $cw->workout_date->format('Y-m-d');
            if (! isset($calendarDays[$dateKey])) {
                $calendarDays[$dateKey] = [
                    'date' => $dateKey,
                    'formatted_date' => $cw->workout_date->translatedFormat('d M Y'),
                    'day_name' => $cw->workout_date->translatedFormat('l'),
                    'items' => [],
                ];
            }

            $calendarDays[$dateKey]['items'][] = [
                'id' => "cw_{$cw->id}",
                'type' => 'custom_workout',
                'program_name' => 'Latihan Mandiri',
                'session_name' => $cw->description ?: 'Latihan Kustom',
                'activity_type' => $cw->type ?: 'run',
                'distance_km' => $cw->distance,
                'duration_min' => $cw->duration,
                'status' => $cw->status ?: 'pending',
                'notes' => $cw->notes,
                'completed_at' => optional($cw->completed_at)->toISOString(),
            ];
        }

        // 3. Fetch Saved Race Events
        $audit = $user->audit_history ?? [];
        $savedIds = $audit['saved_event_ids'] ?? [];
        if (! empty($savedIds)) {
            $savedEvents = Event::whereIn('id', $savedIds)
                ->whereBetween('start_at', [$startOfMonth->toDateTimeString(), $endOfMonth->toDateTimeString()])
                ->get();

            foreach ($savedEvents as $ev) {
                $eventDate = Carbon::parse($ev->start_at);
                $dateKey = $eventDate->format('Y-m-d');
                if (! isset($calendarDays[$dateKey])) {
                    $calendarDays[$dateKey] = [
                        'date' => $dateKey,
                        'formatted_date' => $eventDate->translatedFormat('d M Y'),
                        'day_name' => $eventDate->translatedFormat('l'),
                        'items' => [],
                    ];
                }

                $calendarDays[$dateKey]['items'][] = [
                    'id' => "ev_{$ev->id}",
                    'type' => 'race_event',
                    'program_name' => 'Race Day',
                    'session_name' => $ev->name,
                    'location' => $ev->location_name ?: optional($ev->city)->name,
                    'slug' => $ev->slug,
                    'web_url' => route('events.show', $ev->slug),
                    'status' => 'scheduled',
                ];
            }
        }

        // Sort days by date ASC
        ksort($calendarDays);

        return $this->successResponse([
            'month' => $startOfMonth->format('Y-m'),
            'month_name' => $startOfMonth->translatedFormat('F Y'),
            'days' => array_values($calendarDays),
        ], 'Kalender bulanan berhasil dimuat');
    }

    /**
     * Get detailed daily schedule for a specific date
     */
    public function day(Request $request, string $date): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $carbonDate = Carbon::parse($date);
        } catch (\Exception $e) {
            return $this->errorResponse('Format tanggal tidak valid. Gunakan YYYY-MM-DD.', 422);
        }

        $dateStr = $carbonDate->format('Y-m-d');

        // Custom workouts for day
        $customWorkouts = CustomWorkout::where('runner_id', $user->id)
            ->whereDate('workout_date', $dateStr)
            ->get();

        // Completed Strava runs for day
        $stravaRuns = StravaActivity::where('user_id', $user->id)
            ->whereDate('start_date_local', $dateStr)
            ->get();

        return $this->successResponse([
            'date' => $dateStr,
            'formatted_date' => $carbonDate->translatedFormat('l, d F Y'),
            'custom_workouts' => $customWorkouts,
            'strava_activities' => $stravaRuns,
        ], 'Detail jadwal harian berhasil dimuat');
    }

    /**
     * Mark a program session as complete
     */
    public function completeSession(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
            'rpe' => 'nullable|integer|min:1|max:10',
            'feeling' => 'nullable|string|max:50',
            'strava_link' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        $tracking = ProgramSessionTracking::whereHas('enrollment', fn ($q) => $q->where('runner_id', $user->id))
            ->find($id);

        if (! $tracking) {
            return $this->errorResponse('Sesi latihan tidak ditemukan.', 404);
        }

        $tracking->update([
            'status' => 'completed',
            'completed_at' => now(),
            'notes' => $request->notes ?: $tracking->notes,
            'rpe' => $request->rpe ?: $tracking->rpe,
            'feeling' => $request->feeling ?: $tracking->feeling,
            'strava_link' => $request->strava_link ?: $tracking->strava_link,
        ]);

        return $this->successResponse($tracking, 'Sesi latihan telah ditandai selesai');
    }

    /**
     * Adaptive reschedule a program session
     */
    public function rescheduleSession(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'new_date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        $tracking = ProgramSessionTracking::whereHas('enrollment', fn ($q) => $q->where('runner_id', $user->id))
            ->find($id);

        if (! $tracking) {
            return $this->errorResponse('Sesi latihan tidak ditemukan.', 404);
        }

        $tracking->update([
            'rescheduled_date' => $request->new_date,
        ]);

        return $this->successResponse($tracking, 'Jadwal latihan berhasil digeser ke tanggal ' . $request->new_date);
    }

    /**
     * Create a custom workout entry for runner
     */
    public function storeCustomWorkout(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'workout_date' => 'required|date_format:Y-m-d',
            'type' => 'required|string|max:50',
            'distance' => 'nullable|numeric|min:0',
            'duration' => 'nullable|numeric|min:0',
            'description' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi latihan kustom gagal', 422, $validator->errors());
        }

        $cw = CustomWorkout::create([
            'runner_id' => $user->id,
            'workout_date' => $request->workout_date,
            'type' => $request->type,
            'distance' => $request->distance,
            'duration' => $request->duration,
            'description' => $request->description,
            'notes' => $request->notes,
            'status' => 'pending',
            'source' => 'mobile_app',
        ]);

        return $this->successResponse($cw, 'Latihan kustom berhasil ditambahkan ke kalender', 201);
    }
}
