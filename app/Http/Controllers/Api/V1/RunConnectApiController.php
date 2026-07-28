<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\RunThreadResource;
use App\Models\Notification;
use App\Models\RunThread;
use App\Models\RunThreadMessage;
use App\Models\RunThreadParticipant;
use App\Models\User;
use App\Models\UserRating;
use App\Services\OpenAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RunConnectApiController extends BaseApiController
{
    /**
     * Browse running threads / Cari Teman Lari with location & filters
     */
    public function index(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'radius' => 'nullable|numeric|in:1,3,5,10,25,50',
            'distance_filter' => 'nullable|string|in:3_5,5_10,10_15,15_plus',
            'pace_filter' => 'nullable|string|in:relaxed,7_plus,6_7,5_6,sub_5',
            'start_time_filter' => 'nullable|string|in:now,today,tonight,tomorrow_morning,weekend',
            'beginner_friendly' => 'nullable|boolean',
            'women_friendly' => 'nullable|boolean',
            'search' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi filter gagal', 422, $validator->errors());
        }

        $query = RunThread::query()
            ->with(['creator', 'participants.user'])
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'completed')
            ->where('start_date', '>=', now()->toDateString());

        // Location-based Haversine query
        $hasCoords = $request->filled('latitude') && $request->filled('longitude');
        if ($hasCoords) {
            $lat = (float) $request->latitude;
            $lng = (float) $request->longitude;
            $radius = (float) $request->get('radius', 10);
            $query->closeTo($lat, $lng, $radius);
        }

        // Distance filter
        if ($request->filled('distance_filter')) {
            match ($request->distance_filter) {
                '3_5' => $query->whereBetween('run_distance_km', [3, 5]),
                '5_10' => $query->whereBetween('run_distance_km', [5, 10]),
                '10_15' => $query->whereBetween('run_distance_km', [10, 15]),
                '15_plus' => $query->where('run_distance_km', '>=', 15),
                default => null,
            };
        }

        // Pace filter
        if ($request->filled('pace_filter')) {
            $pace = $request->pace_filter;
            if ($pace === 'relaxed') {
                $query->where('target_pace', 'like', '%Santai%');
            } elseif ($pace === '7_plus') {
                $query->where('target_pace', 'like', '%7%');
            } elseif ($pace === '6_7') {
                $query->where('target_pace', 'like', '%6%');
            } elseif ($pace === '5_6') {
                $query->where('target_pace', 'like', '%5%');
            } elseif ($pace === 'sub_5') {
                $query->where('target_pace', 'like', '%Sub%');
            }
        }

        // Start time filter
        if ($request->filled('start_time_filter')) {
            $stf = $request->start_time_filter;
            if ($stf === 'today') {
                $query->whereDate('start_date', now()->toDateString());
            } elseif ($stf === 'tomorrow_morning') {
                $query->whereDate('start_date', now()->addDay()->toDateString());
            } elseif ($stf === 'weekend') {
                $query->whereIn(DB::raw('DAYOFWEEK(start_date)'), [1, 7]);
            }
        }

        if ($request->boolean('beginner_friendly')) {
            $query->where('beginner_friendly', true);
        }
        if ($request->boolean('women_friendly')) {
            $query->where('women_only', true);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('start_location_name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $threads = $query->orderBy('start_date', 'asc')
            ->orderBy('start_time', 'asc')
            ->paginate($request->get('per_page', 15));

        return $this->successResponse([
            'threads' => RunThreadResource::collection($threads),
            'pagination' => [
                'total' => $threads->total(),
                'per_page' => $threads->perPage(),
                'current_page' => $threads->currentPage(),
                'last_page' => $threads->lastPage(),
            ],
        ], 'Daftar sesi lari bersama berhasil dimuat');
    }

    /**
     * Get detail of a running thread
     */
    public function show(int $id): JsonResponse
    {
        $thread = RunThread::with(['creator', 'participants.user'])->find($id);

        if (! $thread) {
            return $this->errorResponse('Thread lari bersama tidak ditemukan.', 404);
        }

        $thread->increment('views_count');

        return $this->successResponse(new RunThreadResource($thread), 'Detail sesi lari berhasil dimuat');
    }

    /**
     * Create a new running thread / ajakan lari bersama
     */
    public function store(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'start_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'start_time' => 'required|string',
            'start_location_name' => 'required|string|max:255',
            'start_latitude' => 'required|numeric',
            'start_longitude' => 'required|numeric',
            'run_distance_km' => 'required|numeric|min:0.5|max:100',
            'target_pace' => 'required|string|max:50',
            'max_participants' => 'required|integer|min:2|max:50',
            'notes' => 'nullable|string|max:1000',
            'beginner_friendly' => 'nullable|boolean',
            'women_only' => 'nullable|boolean',
            'approval_required' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi ajakan lari gagal', 422, $validator->errors());
        }

        $thread = RunThread::create([
            'user_id' => $user->id,
            'title' => trim($request->title),
            'start_date' => $request->start_date,
            'start_time' => $request->start_time,
            'start_location_name' => trim($request->start_location_name),
            'start_latitude' => $request->start_latitude,
            'start_longitude' => $request->start_longitude,
            'run_distance_km' => $request->run_distance_km,
            'target_pace' => $request->target_pace,
            'max_participants' => $request->max_participants,
            'notes' => $request->notes,
            'beginner_friendly' => $request->boolean('beginner_friendly'),
            'women_only' => $request->boolean('women_only'),
            'approval_required' => $request->boolean('approval_required'),
            'status' => 'open',
        ]);

        // Creator automatically joins as host
        RunThreadParticipant::create([
            'run_thread_id' => $thread->id,
            'user_id' => $user->id,
            'status' => 'joined',
            'is_host' => true,
        ]);

        return $this->successResponse(new RunThreadResource($thread->fresh(['creator', 'participants.user'])), 'Ajakan lari bersama berhasil dibuat', 201);
    }

    /**
     * Generate AI invitation description for running thread
     */
    public function generateDescription(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'distance' => 'nullable|numeric',
            'pace' => 'nullable|string',
            'location' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi gagal', 422, $validator->errors());
        }

        $title = $request->title;
        $distance = $request->distance ?: 5;
        $pace = $request->pace ?: 'Santai';
        $location = $request->location ?: 'Taman Kota';

        $description = "Yuk lari bareng di {$location}! Agenda kita: {$title} dengan jarak sekitar {$distance}km, pace {$pace}. Terbuka untuk teman lari baru, yuk bergabung!";

        if (! empty(config('services.openai.api_key'))) {
            try {
                $openAiService = app(OpenAiService::class);
                $prompt = "Buatkan deskripsi ajakan lari bersama (Cari Teman Lari / Running Connect) yang ramah, mengajak, dan seru dalam Bahasa Indonesia (maksimal 3 kalimat).\nJudul: {$title}\nJarak: {$distance}km\nPace: {$pace}\nLokasi: {$location}";
                $aiResp = $openAiService->getAiResponse($prompt, 'Anda adalah asisten komunitas lari RuangLari.');
                if (is_string($aiResp) && trim($aiResp) !== '') {
                    $description = trim($aiResp);
                }
            } catch (\Throwable $e) {
                // Fallback retained
            }
        }

        return $this->successResponse(['description' => $description], 'Deskripsi AI berhasil dibuat');
    }

    /**
     * Join a running thread
     */
    public function join(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $thread = RunThread::with('participants')->find($id);

        if (! $thread) {
            return $this->errorResponse('Thread lari tidak ditemukan.', 404);
        }

        if ($thread->status === 'cancelled' || $thread->status === 'completed') {
            return $this->errorResponse('Sesi lari ini sudah dibatalkan atau selesai.', 400);
        }

        $existing = RunThreadParticipant::where('run_thread_id', $thread->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            return $this->successResponse([
                'status' => $existing->status,
                'already_joined' => true,
            ], 'Anda sudah bergabung atau mengajukan ketersediaan untuk sesi ini.');
        }

        $joinedCount = $thread->participants->where('status', 'joined')->count();
        if ($joinedCount >= $thread->max_participants) {
            return $this->errorResponse('Kuota peserta sesi lari ini sudah penuh.', 400);
        }

        $status = $thread->approval_required ? 'pending' : 'joined';

        $participant = RunThreadParticipant::create([
            'run_thread_id' => $thread->id,
            'user_id' => $user->id,
            'status' => $status,
        ]);

        // Send notification to host if approval required or user joined
        if ($thread->user_id !== $user->id) {
            $msg = $status === 'pending'
                ? "{$user->name} mengajukan diri untuk bergabung di '{$thread->title}'."
                : "{$user->name} telah bergabung di '{$thread->title}'.";

            Notification::create([
                'user_id' => $thread->user_id,
                'title' => 'Permintaan Teman Lari',
                'message' => $msg,
                'type' => 'run_connect',
                'reference_id' => $thread->id,
            ]);
        }

        $message = $status === 'pending'
            ? 'Pengajuan bergabung berhasil dikirim, menunggu persetujuan pembuat sesi.'
            : 'Selamat! Anda berhasil bergabung ke sesi lari bersama ini.';

        return $this->successResponse([
            'participant_id' => $participant->id,
            'status' => $status,
        ], $message);
    }

    /**
     * Leave a running thread
     */
    public function leave(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $thread = RunThread::find($id);

        if (! $thread) {
            return $this->errorResponse('Thread lari tidak ditemukan.', 404);
        }

        if ($thread->user_id === $user->id) {
            return $this->errorResponse('Pembuat sesi tidak dapat keluar. Anda dapat membatalkan sesi jika diperlukan.', 400);
        }

        $deleted = RunThreadParticipant::where('run_thread_id', $thread->id)
            ->where('user_id', $user->id)
            ->delete();

        if (! $deleted) {
            return $this->errorResponse('Anda belum bergabung ke sesi lari ini.', 400);
        }

        return $this->successResponse(null, 'Anda telah keluar dari sesi lari ini');
    }

    /**
     * Host approves a pending participant
     */
    public function approveParticipant(Request $request, int $id, int $participantId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $thread = RunThread::find($id);
        if (! $thread || $thread->user_id !== $user->id) {
            return $this->errorResponse('Anda tidak memiliki akses untuk menyetujui sesi ini.', 403);
        }

        $participant = RunThreadParticipant::where('run_thread_id', $id)
            ->where('id', $participantId)
            ->first();

        if (! $participant) {
            return $this->errorResponse('Peserta tidak ditemukan.', 404);
        }

        $participant->update(['status' => 'joined']);

        Notification::create([
            'user_id' => $participant->user_id,
            'title' => 'Pengajuan Disetujui',
            'message' => "Pengajuan Anda untuk sesi lari '{$thread->title}' telah disetujui host!",
            'type' => 'run_connect',
            'reference_id' => $thread->id,
        ]);

        return $this->successResponse(null, 'Peserta berhasil disetujui');
    }

    /**
     * Host rejects a pending participant
     */
    public function rejectParticipant(Request $request, int $id, int $participantId): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $thread = RunThread::find($id);
        if (! $thread || $thread->user_id !== $user->id) {
            return $this->errorResponse('Anda tidak memiliki akses untuk menolak sesi ini.', 403);
        }

        $participant = RunThreadParticipant::where('run_thread_id', $id)
            ->where('id', $participantId)
            ->first();

        if (! $participant) {
            return $this->errorResponse('Peserta tidak ditemukan.', 404);
        }

        $participant->update(['status' => 'rejected']);

        return $this->successResponse(null, 'Pengajuan peserta ditolak');
    }

    /**
     * Get thread group chat messages
     */
    public function getMessages(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $thread = RunThread::find($id);
        if (! $thread) {
            return $this->errorResponse('Thread lari tidak ditemukan.', 404);
        }

        $isJoined = RunThreadParticipant::where('run_thread_id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'joined')
            ->exists();

        if (! $isJoined && $thread->user_id !== $user->id) {
            return $this->errorResponse('Hanya peserta yang telah bergabung yang dapat melihat pesan obrolan.', 403);
        }

        $messages = RunThreadMessage::where('run_thread_id', $id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'sender_id' => $m->user_id,
                'sender_name' => optional($m->sender)->name,
                'sender_avatar' => optional($m->sender)->avatar_url,
                'message' => $m->message,
                'created_at' => optional($m->created_at)->toISOString(),
            ]);

        return $this->successResponse($messages, 'Pesan obrolan berhasil dimuat');
    }

    /**
     * Send group chat message in thread
     */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Pesan tidak boleh kosong', 422, $validator->errors());
        }

        $thread = RunThread::find($id);
        if (! $thread) {
            return $this->errorResponse('Thread lari tidak ditemukan.', 404);
        }

        $isJoined = RunThreadParticipant::where('run_thread_id', $id)
            ->where('user_id', $user->id)
            ->where('status', 'joined')
            ->exists();

        if (! $isJoined && $thread->user_id !== $user->id) {
            return $this->errorResponse('Hanya peserta yang bergabung yang dapat menginstruksikan pesan.', 403);
        }

        $msg = RunThreadMessage::create([
            'run_thread_id' => $id,
            'user_id' => $user->id,
            'message' => trim($request->message),
        ]);

        return $this->successResponse([
            'id' => $msg->id,
            'sender_id' => $user->id,
            'sender_name' => $user->name,
            'sender_avatar' => $user->avatar_url,
            'message' => $msg->message,
            'created_at' => optional($msg->created_at)->toISOString(),
        ], 'Pesan terkirim', 201);
    }

    /**
     * Rate running buddy after a completed session
     */
    public function rateBuddy(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'target_user_id' => 'required|integer|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validasi rating gagal', 422, $validator->errors());
        }

        $targetUserId = (int) $request->target_user_id;
        if ($targetUserId === $user->id) {
            return $this->errorResponse('Anda tidak dapat memberikan rating pada diri sendiri.', 400);
        }

        UserRating::updateOrCreate([
            'rater_id' => $user->id,
            'rated_id' => $targetUserId,
            'run_thread_id' => $id,
        ], [
            'rating' => $request->rating,
            'feedback' => $request->feedback,
        ]);

        // Update average rating for target user
        $avg = UserRating::where('rated_id', $targetUserId)->avg('rating');
        User::where('id', $targetUserId)->update(['buddy_rating' => round($avg, 1)]);

        return $this->successResponse(null, 'Terima kasih! Rating teman lari berhasil disimpan');
    }

    /**
     * Get random running match / nearby active runners
     */
    public function randomMatch(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $query = RunThread::where('user_id', '!=', $user->id)
            ->where('status', 'open')
            ->where('start_date', '>=', now()->toDateString())
            ->with(['creator', 'participants.user']);

        if ($user->latitude && $user->longitude) {
            $query->closeTo((float) $user->latitude, (float) $user->longitude, 25);
        }

        $threads = $query->inRandomOrder()->take(5)->get();

        return $this->successResponse(RunThreadResource::collection($threads), 'Rekomendasi teman lari acak berhasil dimuat');
    }

    /**
     * Get runner's created & joined thread history
     */
    public function history(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $createdThreads = RunThread::where('user_id', $user->id)
            ->with(['creator', 'participants.user'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $joinedThreads = RunThread::whereHas('participants', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('status', 'joined');
        })
            ->where('user_id', '!=', $user->id)
            ->with(['creator', 'participants.user'])
            ->orderBy('start_date', 'desc')
            ->take(20)
            ->get();

        return $this->successResponse([
            'created' => RunThreadResource::collection($createdThreads),
            'joined' => RunThreadResource::collection($joinedThreads),
        ], 'Riwayat sesi lari berhasil dimuat');
    }

    /**
     * Get pending approvals for host runner
     */
    public function approvals(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $pendingParticipants = RunThreadParticipant::whereHas('thread', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->where('status', 'pending')
            ->with(['user', 'thread'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($p) => [
                'participant_id' => $p->id,
                'thread_id' => $p->run_thread_id,
                'thread_title' => optional($p->thread)->title,
                'user' => [
                    'id' => $p->user->id,
                    'name' => $p->user->name,
                    'username' => $p->user->username,
                    'avatar_url' => $p->user->avatar_url,
                    'buddy_rating' => $p->user->buddy_rating ?: 5.0,
                ],
                'requested_at' => optional($p->created_at)->toISOString(),
            ]);

        return $this->successResponse($pendingParticipants, 'Daftar pengajuan persetujuan berhasil dimuat');
    }

    /**
     * Get top running buddy leaderboard
     */
    public function leaderboard(Request $request): JsonResponse
    {
        $topBuddies = User::whereNotNull('buddy_rating')
            ->where('buddy_rating', '>', 0)
            ->orderBy('buddy_rating', 'desc')
            ->take(10)
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'avatar_url' => $u->avatar_url,
                'buddy_rating' => $u->buddy_rating,
            ]);

        return $this->successResponse($topBuddies, 'Leaderboard Teman Lari berhasil dimuat');
    }
}
