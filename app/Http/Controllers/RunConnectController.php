<?php

namespace App\Http\Controllers;

use App\Models\RunThread;
use App\Models\RunThreadParticipant;
use App\Models\RunThreadReport;
use App\Models\RunThreadMessage;
use App\Models\UserRating;
use App\Models\UserAchievement;
use App\Models\Notification;
use App\Jobs\SendRunConnectNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;

class RunConnectController extends Controller
{
    /**
     * Render the Run Connect Main Page.
     */
    public function index()
    {
        return Inertia::render('RunConnect', [
            'mapboxToken' => config('services.mapbox.token'),
            'auth' => [
                'user' => Auth::user(),
            ]
        ]);
    }

    /**
     * Fetch nearby running threads with filters.
     */
    public function getThreads(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius' => 'nullable|numeric|in:1,3,5,10,25',
            'type' => 'nullable|string',
            'distance_filter' => 'nullable|string|in:3_5,5_10,10_15,15_plus',
            'pace_filter' => 'nullable|string|in:relaxed,7_plus,6_7,5_6,sub_5',
            'start_time_filter' => 'nullable|string|in:now,today,tonight,tomorrow_morning,weekend',
            'slot_available' => 'nullable|string',
            'beginner_friendly' => 'nullable|string',
            'women_friendly' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = RunThread::with(['creator', 'participants' => function($q) {
            $q->where('status', 'joined')->with('user');
        }]);

        // Base criteria: exclude cancelled and completed threads, only future or current date threads
        $query->where('status', '!=', 'cancelled')
              ->where('status', '!=', 'completed')
              ->where('start_date', '>=', now()->toDateString());

        // Location-based filtering (Haversine)
        $hasCoords = $request->filled('latitude') && $request->filled('longitude');
        if ($hasCoords) {
            $lat = (float) $request->latitude;
            $lng = (float) $request->longitude;
            $radius = (float) $request->get('radius', 5);
            $query->closeTo($lat, $lng, $radius);
        }

        // Distance filter (Run Distance KM)
        if ($request->filled('distance_filter')) {
            $df = $request->distance_filter;
            if ($df === '3_5') {
                $query->whereBetween('run_distance_km', [3, 5]);
            } elseif ($df === '5_10') {
                $query->whereBetween('run_distance_km', [5, 10]);
            } elseif ($df === '10_15') {
                $query->whereBetween('run_distance_km', [10, 15]);
            } elseif ($df === '15_plus') {
                $query->where('run_distance_km', '>=', 15);
            }
        }

        // Pace filter
        if ($request->filled('pace_filter')) {
            $pf = $request->pace_filter;
            if ($pf === 'relaxed') {
                $query->where(function($q) {
                    $q->where('pace_min', '>=', '7:00')
                      ->orWhereNull('pace_min');
                });
            } elseif ($pf === '7_plus') {
                $query->where('pace_min', '>=', '7:00');
            } elseif ($pf === '6_7') {
                $query->where(function($q) {
                    $q->whereBetween('pace_min', ['6:00', '7:00'])
                      ->orWhereBetween('pace_max', ['6:00', '7:00']);
                });
            } elseif ($pf === '5_6') {
                $query->where(function($q) {
                    $q->whereBetween('pace_min', ['5:00', '6:00'])
                      ->orWhereBetween('pace_max', ['5:00', '6:00']);
                });
            } elseif ($pf === 'sub_5') {
                $query->where('pace_max', '<=', '5:00');
            }
        }

        // Start time filter
        if ($request->filled('start_time_filter')) {
            $stf = $request->start_time_filter;
            if ($stf === 'now') {
                $query->where('start_date', now()->toDateString())
                      ->where('start_time', '>=', now()->toTimeString())
                      ->where('start_time', '<=', now()->addHours(3)->toTimeString());
            } elseif ($stf === 'today') {
                $query->where('start_date', now()->toDateString());
            } elseif ($stf === 'tonight') {
                $query->where('start_date', now()->toDateString())
                      ->whereTime('start_time', '>=', '18:00:00');
            } elseif ($stf === 'tomorrow_morning') {
                $query->where('start_date', now()->addDay()->toDateString())
                      ->whereTime('start_time', '>=', '04:00:00')
                      ->whereTime('start_time', '<=', '11:00:00');
            } elseif ($stf === 'weekend') {
                $query->whereIn(\DB::raw('DAYOFWEEK(start_date)'), [1, 7]); // 1 = Sunday, 7 = Saturday
            }
        }

        // Tipe lari
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Slot available only
        if ($request->has('slot_available') && $request->slot_available === 'true') {
            $query->whereRaw('(SELECT COUNT(*) FROM run_thread_participants WHERE run_thread_participants.run_thread_id = run_threads.id AND run_thread_participants.status = "joined") < quota');
        }

        // Beginner friendly
        if ($request->has('beginner_friendly') && $request->beginner_friendly === 'true') {
            $query->where('is_beginner_friendly', true);
        }

        // Women friendly
        if ($request->has('women_friendly') && $request->women_friendly === 'true') {
            $query->where('is_women_friendly', true);
        }

        // Filter Host Gender (Male / Female)
        if ($request->filled('host_gender')) {
            $query->whereHas('creator', function($q) use ($request) {
                $q->where('gender', $request->host_gender);
            });
        }

        // Filter Host Age Range
        if ($request->filled('host_age_range')) {
            $range = $request->host_age_range;
            if ($range === 'under_20') {
                $maxDate = now()->subYears(20)->toDateString();
                $query->whereHas('creator', function($q) use ($maxDate) {
                    $q->where('date_of_birth', '>', $maxDate);
                });
            } elseif ($range === '20_30') {
                $minDate = now()->subYears(20)->toDateString();
                $maxDate = now()->subYears(31)->toDateString();
                $query->whereHas('creator', function($q) use ($minDate, $maxDate) {
                    $q->whereBetween('date_of_birth', [$maxDate, $minDate]);
                });
            } elseif ($range === '30_40') {
                $minDate = now()->subYears(30)->toDateString();
                $maxDate = now()->subYears(41)->toDateString();
                $query->whereHas('creator', function($q) use ($minDate, $maxDate) {
                    $q->whereBetween('date_of_birth', [$maxDate, $minDate]);
                });
            } elseif ($range === 'above_40') {
                $minDate = now()->subYears(40)->toDateString();
                $query->whereHas('creator', function($q) use ($minDate) {
                    $q->where('date_of_birth', '<', $minDate);
                });
            }
        }

        // Ordering & Pagination
        if ($hasCoords) {
            $query->orderBy('distance', 'asc');
        } else {
            $query->orderBy('start_date', 'asc')->orderBy('start_time', 'asc');
        }

        $threads = $query->paginate(20);

        return response()->json($threads);
    }

    /**
     * Get a specific thread detail.
     */
    public function showThread($id)
    {
        $thread = RunThread::with(['creator', 'participants' => function($q) {
            $q->where('status', 'joined')->with('user');
        }])->findOrFail($id);

        return response()->json($thread);
    }

    /**
     * Create a new running thread.
     */
    public function storeThread(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Anda harus login untuk membuat thread.'], 401);
        }

        // Simple rate limiting to prevent spam (only enforced in production)
        $rateLimitKey = 'create-run-thread:' . Auth::id();
        if (app()->environment('production') && RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            return response()->json(['message' => 'Terlalu banyak membuat thread. Silakan coba beberapa saat lagi.'], 429);
        }
        RateLimiter::hit($rateLimitKey, 3600); // 5 per hour

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'type' => 'required|string|in:Casual Run,Long Run,Speed Session,Recovery Run,Race Prep,Community Run',
            'run_distance_km' => 'required|numeric|min:0.5|max:100',
            'pace_min' => 'nullable|string|max:10',
            'pace_max' => 'nullable|string|max:10',
            'start_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|string',
            'start_location_name' => 'required|string|max:150',
            'start_latitude' => 'required|numeric|between:-90,90',
            'start_longitude' => 'required|numeric|between:-180,180',
            'route_url' => 'nullable|url|max:255',
            'quota' => 'required|integer|min:2|max:100',
            'visibility' => 'required|string|in:public,community',
            'is_beginner_friendly' => 'boolean',
            'is_women_friendly' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['creator_id'] = Auth::id();
        $data['status'] = 'open';

        $thread = RunThread::create($data);

        // Creator automatically joins the thread
        RunThreadParticipant::create([
            'run_thread_id' => $thread->id,
            'user_id' => Auth::id(),
            'status' => 'joined',
            'joined_at' => now(),
        ]);

        // Gamification: Create Thread
        $user = Auth::user();
        UserAchievement::create([
            'user_id' => $user->id,
            'activity_type' => 'create_thread',
            'points' => 50,
            'reference_id' => (string) $thread->id,
        ]);
        $user->increment('run_points', 50);

        return response()->json([
            'message' => 'Running thread berhasil dibuat!',
            'thread' => $thread->load(['creator', 'participants.user'])
        ], 201);
    }

    /**
     * Join an open running thread.
     */
    public function joinThread($id)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $userId = Auth::id();
        $thread = RunThread::with('participants')->findOrFail($id);

        if ($thread->status !== 'open') {
            return response()->json(['message' => 'Thread lari ini sudah dimulai, penuh, atau dibatalkan.'], 422);
        }

        // Check quota
        $joinedCount = $thread->participants->where('status', 'joined')->count();
        if ($joinedCount >= $thread->quota) {
            // Update status to full
            $thread->update(['status' => 'full']);
            return response()->json(['message' => 'Kuota lari ini sudah penuh.'], 422);
        }

        // Check if user is already joined
        $existing = RunThreadParticipant::where('run_thread_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            if ($existing->status === 'joined') {
                return response()->json(['message' => 'Anda sudah bergabung dengan lari ini.'], 422);
            }
            if ($existing->status === 'pending') {
                return response()->json(['message' => 'Permintaan bergabung Anda sedang diproses oleh host.'], 422);
            }
            // Update back to pending
            $existing->update([
                'status' => 'pending',
                'joined_at' => null
            ]);
        } else {
            RunThreadParticipant::create([
                'run_thread_id' => $id,
                'user_id' => $userId,
                'status' => 'pending',
                'joined_at' => null
            ]);
        }

        // Create join request notification for the thread creator
        if ($thread->creator_id !== $userId) {
            SendRunConnectNotification::dispatch(
                $thread->creator_id,
                'run_connect_request',
                'Permintaan Bergabung',
                Auth::user()->name . ' ingin bergabung ke running thread Anda: ' . $thread->title,
                'RunThread',
                $thread->id
            );
        }

        return response()->json([
            'message' => 'Permintaan bergabung berhasil dikirim ke host!',
            'thread' => $thread->load(['creator', 'participants' => function($q) {
                $q->with('user');
            }])
        ]);
    }

    /**
     * Leave a running thread.
     */
    public function leaveThread($id)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $userId = Auth::id();
        $thread = RunThread::findOrFail($id);

        // Creator cannot leave their own thread, they must cancel/delete it
        if ($thread->creator_id === $userId) {
            return response()->json(['message' => 'Sebagai pembuat thread, Anda tidak bisa keluar. Anda bisa mengubah status lari menjadi batal.'], 422);
        }

        $participant = RunThreadParticipant::where('run_thread_id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$participant || $participant->status !== 'joined') {
            return response()->json(['message' => 'Anda belum bergabung dengan lari ini.'], 422);
        }

        $participant->update(['status' => 'left']);

        // Check if thread was full and is now open again
        if ($thread->status === 'full') {
            $thread->update(['status' => 'open']);
        }

        return response()->json([
            'message' => 'Anda berhasil keluar dari program lari.',
            'thread' => $thread->load(['creator', 'participants' => function($q) {
                $q->where('status', 'joined')->with('user');
            }])
        ]);
    }

    /**
     * Report a running thread.
     */
    public function reportThread(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:100',
            'description' => 'nullable|string|max:300',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        RunThreadReport::create([
            'run_thread_id' => $id,
            'reporter_id' => Auth::id(),
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Thread berhasil dilaporkan. Terima kasih atas laporan Anda.']);
    }

    /**
     * Random Match algorithm.
     */
    public function randomMatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'preferred_distance' => 'nullable|numeric', // target distance in km
            'preferred_pace' => 'nullable|string',      // e.g., '6:00', '5:30'
            'preferred_type' => 'nullable|string',      // e.g., 'Casual Run'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;

        // Fetch open threads within 25km
        $threads = RunThread::with(['creator', 'participants' => function($q) {
            $q->where('status', 'joined')->with('user');
        }])
        ->where('status', 'open')
        ->where('start_date', '>=', now()->toDateString())
        ->closeTo($lat, $lng, 25)
        ->get();

        if ($threads->isEmpty()) {
            return response()->json([
                'message' => 'Tidak menemukan program lari terdekat yang cocok. Coba perlebar radius atau ubah kriteria.',
                'matches' => []
            ]);
        }

        $scoredThreads = $threads->map(function ($thread) use ($lat, $lng, $request) {
            $score = 0;

            // 1. Distance proximity (Max 40 points)
            // Less than 2km = 40 pts, 2-5km = 30 pts, 5-10km = 20 pts, 10-25km = 10 pts
            $distance = $thread->distance ?? 25.0;
            if ($distance <= 2) {
                $score += 40;
            } elseif ($distance <= 5) {
                $score += 30;
            } elseif ($distance <= 10) {
                $score += 20;
            } else {
                $score += 10;
            }

            // 2. Preferred Run Distance Match (Max 25 points)
            if ($request->filled('preferred_distance')) {
                $diff = abs($thread->run_distance_km - (float) $request->preferred_distance);
                if ($diff <= 1) {
                    $score += 25;
                } elseif ($diff <= 3) {
                    $score += 15;
                } elseif ($diff <= 5) {
                    $score += 5;
                }
            } else {
                $score += 15; // default moderate score
            }

            // 3. Preferred Run Type Match (Max 20 points)
            if ($request->filled('preferred_type') && $thread->type === $request->preferred_type) {
                $score += 20;
            } else {
                $score += 10;
            }

            // 4. Time Factor (Max 15 points)
            // If the run is today, give it a higher match score
            if ($thread->start_date->toDateString() === now()->toDateString()) {
                $score += 15;
            } else {
                $score += 8;
            }

            return [
                'thread' => $thread,
                'match_score' => min($score, 100),
                'distance' => round($distance, 1)
            ];
        });

        // Sort by match score descending
        $sorted = $scoredThreads->sortByDesc('match_score')->values()->take(3);

        return response()->json([
            'matches' => $sorted
        ]);
    }

    /**
     * Get chat messages for a thread
     */
    public function getMessages($id)
    {
        $thread = RunThread::findOrFail($id);
        
        // Ensure user is participant and status is joined
        if (!Auth::check() || !$thread->participants()->where('user_id', Auth::id())->where('status', 'joined')->exists()) {
            return response()->json(['message' => 'Unauthorized. Anda harus bergabung dengan status disetujui terlebih dahulu.'], 403);
        }

        $messages = $thread->messages()->with('user:id,name,avatar')->orderBy('created_at', 'asc')->get();
        return response()->json(['messages' => $messages]);
    }

    /**
     * Send a chat message
     */
    public function sendMessage(Request $request, $id)
    {
        $request->validate(['message' => 'required|string|max:1000']);
        $thread = RunThread::findOrFail($id);

        if (!Auth::check() || !$thread->participants()->where('user_id', Auth::id())->where('status', 'joined')->exists()) {
            return response()->json(['message' => 'Unauthorized. Anda harus bergabung dengan status disetujui terlebih dahulu.'], 403);
        }

        // Optional: lock chat if thread is passed H+1 (left out for simplicity here, can add later)

        $msg = $thread->messages()->create([
            'user_id' => Auth::id(),
            'message' => $request->message
        ]);

        // Send notifications to other participants (queued)
        $participantIds = $thread->users()->where('user_id', '!=', Auth::id())->pluck('user_id')->toArray();
        if (!empty($participantIds)) {
            SendRunConnectNotification::dispatch(
                $participantIds,
                'run_connect_message',
                'Pesan Baru',
                '[Chat] ' . Auth::user()->name . ': ' . $request->message,
                'RunThread',
                $thread->id
            );
        }

        return response()->json([
            'message' => 'Message sent',
            'data' => $msg->load('user:id,name,avatar')
        ]);
    }

    /**
     * Upload GPX file for a thread (creator only)
     */
    public function uploadGpx(Request $request, $id)
    {
        $request->validate([
            'gpx_file' => 'required|file|mimes:gpx,xml|max:5120' // max 5MB
        ]);

        $thread = RunThread::findOrFail($id);
        
        if ($thread->creator_id !== Auth::id()) {
            return response()->json(['message' => 'Only the creator can upload GPX.'], 403);
        }

        $path = $request->file('gpx_file')->store('gpx', 'public');
        $thread->update(['gpx_file_path' => '/storage/' . $path]);

        return response()->json(['message' => 'GPX file uploaded successfully.', 'path' => $thread->gpx_file_path]);
    }

    /**
     * Rate another user in the thread
     */
    public function rateThread(Request $request, $id)
    {
        $request->validate([
            'reviewee_id' => 'required|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500'
        ]);

        $thread = RunThread::findOrFail($id);
        $reviewerId = Auth::id();

        // Check if both are participants
        if (!$thread->users()->where('user_id', $reviewerId)->exists() || 
            !$thread->users()->where('user_id', $request->reviewee_id)->exists()) {
            return response()->json(['message' => 'Both users must be participants.'], 403);
        }

        if ($reviewerId == $request->reviewee_id) {
            return response()->json(['message' => 'Cannot rate yourself.'], 400);
        }

        // Create or update rating
        UserRating::updateOrCreate(
            ['reviewer_id' => $reviewerId, 'reviewee_id' => $request->reviewee_id, 'run_thread_id' => $id],
            ['rating' => $request->rating, 'comment' => $request->comment]
        );

        // Update target user's aggregate rating
        $targetUser = \App\Models\User::find($request->reviewee_id);
        $avgRating = $targetUser->ratingsReceived()->avg('rating');
        $targetUser->update(['buddy_rating' => $avgRating]);

        return response()->json(['message' => 'Rating submitted successfully.']);
    }

    /**
     * Get recent notifications for authenticated user
     */
    public function getNotifications()
    {
        if (!Auth::check()) {
            return response()->json(['notifications' => []]);
        }

        $notifications = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Mark all notifications as read
     */
    public function readAllNotifications()
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Mark specific notification as read
     */
    public function readNotification($id)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Update an existing thread (Creator only)
     */
    public function updateThread(Request $request, $id)
    {
        $thread = RunThread::findOrFail($id);

        if ($thread->creator_id !== Auth::id()) {
            return response()->json(['message' => 'Hanya pembuat thread yang dapat mengedit thread ini.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'type' => 'required|string|in:Casual Run,Long Run,Speed Session,Recovery Run,Race Prep,Community Run',
            'run_distance_km' => 'required|numeric|min:0.5|max:100',
            'pace_min' => 'nullable|string|max:10',
            'pace_max' => 'nullable|string|max:10',
            'start_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|string',
            'start_location_name' => 'required|string|max:150',
            'start_latitude' => 'required|numeric|between:-90,90',
            'start_longitude' => 'required|numeric|between:-180,180',
            'route_url' => 'nullable|url|max:255',
            'quota' => 'required|integer|min:2|max:100',
            'visibility' => 'required|string|in:public,community',
            'is_beginner_friendly' => 'boolean',
            'is_women_friendly' => 'boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $thread->update($validator->validated());

        // Notify other participants about the update (queued)
        $participantIds = $thread->users()->where('user_id', '!=', Auth::id())->pluck('user_id')->toArray();
        if (!empty($participantIds)) {
            SendRunConnectNotification::dispatch(
                $participantIds,
                'run_connect_update',
                'Thread Lari Diperbarui',
                'Detail lari untuk thread "' . $thread->title . '" telah diperbarui oleh host.',
                'RunThread',
                $thread->id
            );
        }

        return response()->json([
            'message' => 'Running thread berhasil diperbarui!',
            'thread' => $thread->load(['creator', 'participants.user'])
        ]);
    }

    /**
     * Delete/cancel a thread (Creator only)
     */
    public function destroyThread($id)
    {
        $thread = RunThread::findOrFail($id);

        if ($thread->creator_id !== Auth::id()) {
            return response()->json(['message' => 'Hanya pembuat thread yang dapat membatalkan thread ini.'], 403);
        }

        // Notify other participants about cancellation before soft deleting (queued)
        $participantIds = $thread->users()->where('user_id', '!=', Auth::id())->pluck('user_id')->toArray();
        if (!empty($participantIds)) {
            SendRunConnectNotification::dispatch(
                $participantIds,
                'run_connect_cancel',
                'Thread Lari Dibatalkan',
                'Running thread "' . $thread->title . '" telah dibatalkan oleh host.',
                'RunThread',
                $thread->id
            );
        }

        // Soft delete thread and participants
        $thread->update(['status' => 'cancelled']);
        $thread->delete();

        return response()->json(['message' => 'Running thread berhasil dibatalkan.']);
    }

    /**
     * Approve a pending participant (Host only)
     */
    public function approveParticipant($threadId, $participantId)
    {
        $thread = RunThread::findOrFail($threadId);
        
        if ($thread->creator_id !== Auth::id()) {
            return response()->json(['message' => 'Hanya pembuat thread yang dapat menyetujui peserta.'], 403);
        }

        // Get participant
        $participant = RunThreadParticipant::where('run_thread_id', $threadId)
            ->where('id', $participantId)
            ->firstOrFail();

        if ($participant->status === 'joined') {
            return response()->json(['message' => 'Peserta sudah bergabung.'], 422);
        }

        // Check quota
        $joinedCount = $thread->participants->where('status', 'joined')->count();
        if ($joinedCount >= $thread->quota) {
            return response()->json(['message' => 'Kuota lari ini sudah penuh.'], 422);
        }

        $participant->update([
            'status' => 'joined',
            'joined_at' => now()
        ]);

        // If capacity reached, update status to full
        if ($joinedCount + 1 >= $thread->quota) {
            $thread->update(['status' => 'full']);
        }

        // Gamification: Give points to approved user
        $user = $participant->user;
        if ($user) {
            UserAchievement::create([
                'user_id' => $user->id,
                'activity_type' => 'join_thread',
                'points' => 10,
                'reference_id' => (string) $threadId,
            ]);
            $user->increment('run_points', 10);
        }

        // Notify participant
        SendRunConnectNotification::dispatch(
            $participant->user_id,
            'run_connect_approved',
            'Permintaan Bergabung Disetujui',
            'Permintaan Anda untuk bergabung ke thread lari "' . $thread->title . '" telah disetujui oleh host.',
            'RunThread',
            $thread->id
        );

        return response()->json([
            'message' => 'Peserta berhasil disetujui!',
            'thread' => $thread->load(['creator', 'participants.user'])
        ]);
    }

    /**
     * Reject a pending participant (Host only)
     */
    public function rejectParticipant($threadId, $participantId)
    {
        $thread = RunThread::findOrFail($threadId);
        
        if ($thread->creator_id !== Auth::id()) {
            return response()->json(['message' => 'Hanya pembuat thread yang dapat menolak peserta.'], 403);
        }

        // Get participant
        $participant = RunThreadParticipant::where('run_thread_id', $threadId)
            ->where('id', $participantId)
            ->firstOrFail();

        if ($participant->status !== 'pending') {
            return response()->json(['message' => 'Peserta tidak dalam status pending.'], 422);
        }

        $participant->update([
            'status' => 'rejected'
        ]);

        // Notify participant
        SendRunConnectNotification::dispatch(
            $participant->user_id,
            'run_connect_rejected',
            'Permintaan Bergabung Ditolak',
            'Permintaan Anda untuk bergabung ke thread lari "' . $thread->title . '" ditolak oleh host.',
            'RunThread',
            $thread->id
        );

        return response()->json([
            'message' => 'Peserta berhasil ditolak.',
            'thread' => $thread->load(['creator', 'participants.user'])
        ]);
    }
}
