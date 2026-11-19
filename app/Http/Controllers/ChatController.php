<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Services\AiCoachContinuityService;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    private function getAiCoach(): ?User
    {
        return app(AiCoachContinuityService::class)->getCoachUser();
    }

    /**
     * Build lightweight conversation summaries without loading full message history
     * or triggering N+1 unread queries.
     */
    private function getConversationSummaries(int $limit = 30): Collection
    {
        $authId = (int) Auth::id();
        $isRunner = (bool) Auth::user()?->isRunner();
        $aiCoach = $isRunner ? $this->getAiCoach() : null;

        // 1. Get latest message ID per conversation partner in a single aggregated query
        $latestIds = Message::query()
            ->selectRaw('MAX(id) as last_id, CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END as partner_id', [$authId])
            ->where(function ($q) use ($authId) {
                $q->where('sender_id', $authId)->orWhere('receiver_id', $authId);
            })
            ->groupBy('partner_id')
            ->orderByDesc('last_id')
            ->limit($limit)
            ->pluck('last_id', 'partner_id');

        // 2. Fetch only those latest messages with sender/receiver
        $latestMessages = $latestIds->isNotEmpty()
            ? Message::whereIn('id', $latestIds->values())
                ->with(['sender', 'receiver'])
                ->get()
                ->keyBy('id')
            : collect();

        // 3. Fetch unread counts per sender in a single grouped query
        $unreadCounts = Message::query()
            ->select('sender_id', DB::raw('COUNT(*) as total'))
            ->where('receiver_id', $authId)
            ->where('is_read', false)
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        $summaries = collect();

        foreach ($latestIds as $partnerId => $lastId) {
            $msg = $latestMessages->get($lastId);
            if (! $msg) {
                continue;
            }

            $otherUser = (int) $msg->sender_id === $authId ? $msg->receiver : $msg->sender;
            if (! $otherUser) {
                continue;
            }

            if ($otherUser->email === 'ai-coach@ruanglari.com' && ! $isRunner) {
                continue;
            }

            $summaries->push([
                'user' => $otherUser,
                'last_message' => $msg,
                'unread_count' => (int) ($unreadCounts->get($otherUser->id) ?? 0),
                'is_ai_coach' => $otherUser->email === 'ai-coach@ruanglari.com',
            ]);
        }

        // Always pin / ensure Coach Ruang Lari appears for runners
        if ($aiCoach) {
            $existingIndex = $summaries->search(fn ($item) => (int) $item['user']->id === (int) $aiCoach->id);
            if ($existingIndex === false) {
                $summaries->prepend([
                    'user' => $aiCoach,
                    'last_message' => null,
                    'unread_count' => 0,
                    'is_ai_coach' => true,
                ]);
            }
        }

        return $summaries->values();
    }

    private function formatMessagePayload(Message $message): array
    {
        $message->loadMissing('sender');

        return [
            'id' => $message->id,
            'sender_id' => $message->sender_id,
            'receiver_id' => $message->receiver_id,
            'message' => $message->message,
            'is_read' => (bool) $message->is_read,
            'created_at' => $message->created_at->toISOString(),
            'sender' => [
                'id' => $message->sender->id,
                'name' => $message->sender->name,
                'avatar' => $message->sender->avatar_url,
            ],
        ];
    }

    public function index()
    {
        $conversations = $this->getConversationSummaries();

        return view('chat.index', [
            'conversations' => $conversations,
            'aiCoach' => Auth::user()?->isRunner() ? $this->getAiCoach() : null,
        ]);
    }

    public function show(User $user)
    {
        if ($user->email === 'ai-coach@ruanglari.com' && ! Auth::user()?->isRunner()) {
            abort(403);
        }

        $authId = (int) Auth::id();
        $conversations = $this->getConversationSummaries();

        // Load latest 100 messages chronologically for fast page render
        $messages = Message::where(function ($query) use ($user, $authId) {
            $query->where('sender_id', $authId)
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user, $authId) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $authId);
        })
            ->with('sender')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        // Mark incoming unread messages as read
        Message::where('sender_id', $user->id)
            ->where('receiver_id', $authId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $chatQuota = $user->email === 'ai-coach@ruanglari.com'
            ? app(AiCoachContinuityService::class)->getCoachChatQuotaStatus(Auth::user())
            : null;

        return view('chat.show', [
            'user' => $user,
            'messages' => $messages,
            'conversations' => $conversations,
            'chatQuota' => $chatQuota,
        ]);
    }

    public function store(Request $request, User $user)
    {
        $isAiCoach = $user->email === 'ai-coach@ruanglari.com';

        if ($isAiCoach && ! Auth::user()?->isRunner()) {
            abort(403);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $continuity = app(AiCoachContinuityService::class);
        if ($isAiCoach) {
            $quota = $continuity->getCoachChatQuotaStatus(Auth::user());
            if (! $quota['can_send']) {
                $limitMessage = $quota['has_active_program']
                    ? "Kuota konsultasi harian Anda ({$quota['limit']} pesan/hari) sudah habis. Anda tetap bisa mengikuti jadwal di Kalender Dashboard dan lanjut konsultasi besok."
                    : "Kuota chat gratis harian Anda ({$quota['limit']} pesan/hari) sudah habis. Buat program lari resmi di /buat-program-lari untuk membuka 15 pesan konsultasi/hari bersama Coach Ruang Lari.";

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'quota_exceeded' => true,
                        'message' => $limitMessage,
                        'chat_quota' => $quota,
                    ], 429);
                }

                return back()->with('error', $limitMessage);
            }
        }

        $authId = (int) Auth::id();

        $message = Message::create([
            'sender_id' => $authId,
            'receiver_id' => $user->id,
            'message' => $request->message,
        ]);

        $aiReplyMessage = null;

        // AI Coach Auto-reply (returned directly in JSON so UI updates immediately without polling lag)
        if ($isAiCoach) {
            $openAiService = app(OpenAiService::class);
            $historyMessages = Message::query()
                ->where('id', '!=', $message->id)
                ->where(function ($query) use ($user, $authId) {
                    $query->where(function ($q) use ($user, $authId) {
                        $q->where('sender_id', $authId)
                            ->where('receiver_id', $user->id);
                    })->orWhere(function ($q) use ($user, $authId) {
                        $q->where('sender_id', $user->id)
                            ->where('receiver_id', $authId);
                    });
                })
                ->orderByDesc('id')
                ->limit(16)
                ->get()
                ->reverse()
                ->values();

            $history = $historyMessages->map(function ($m) use ($authId) {
                $isUser = (int) $m->sender_id === $authId;

                return [
                    'role' => $isUser ? 'user' : 'assistant',
                    'content' => (string) $m->message,
                ];
            })->all();

            $aiResponse = $openAiService->getCoachResponse(Auth::user(), $request->message, $history);

            if ($aiResponse) {
                $aiReplyMessage = Message::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $authId,
                    'message' => $aiResponse,
                    'is_read' => true,
                    'read_at' => now(),
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $this->formatMessagePayload($message),
                'ai_reply' => $aiReplyMessage ? $this->formatMessagePayload($aiReplyMessage) : null,
                'chat_quota' => $isAiCoach ? $continuity->getCoachChatQuotaStatus(Auth::user()) : null,
            ]);
        }

        return back()->with('success', 'Pesan berhasil dikirim');
    }

    public function getConversations()
    {
        $summaries = $this->getConversationSummaries(15);

        $formatted = $summaries->map(function (array $item) {
            /** @var User $otherUser */
            $otherUser = $item['user'];
            /** @var Message|null $lastMessage */
            $lastMessage = $item['last_message'];

            return [
                'user_id' => $otherUser->id,
                'user_name' => $otherUser->name,
                'user_avatar' => $otherUser->avatar_url,
                'user_email' => $otherUser->email,
                'last_message' => $lastMessage
                    ? $lastMessage->message
                    : 'Halo! Saya Coach Ruang Lari. Ada yang bisa saya bantu?',
                'last_message_time' => $lastMessage
                    ? $lastMessage->created_at->toISOString()
                    : now()->toISOString(),
                'unread_count' => $item['unread_count'],
            ];
        })->values();

        return response()->json([
            'conversations' => $formatted,
        ]);
    }

    public function getMessages(Request $request, $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->email === 'ai-coach@ruanglari.com' && ! Auth::user()?->isRunner()) {
            abort(403);
        }

        $authId = (int) Auth::id();
        $afterId = max(0, (int) $request->query('after_id', 0));

        $query = Message::where(function ($q) use ($user, $authId) {
            $q->where('sender_id', $authId)
                ->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($user, $authId) {
            $q->where('sender_id', $user->id)
                ->where('receiver_id', $authId);
        });

        if ($afterId > 0) {
            // Delta polling: fetch ONLY messages newer than after_id
            $messages = $query->where('id', '>', $afterId)
                ->with('sender')
                ->orderBy('id', 'asc')
                ->limit(50)
                ->get();
        } else {
            $messages = $query->with('sender')
                ->orderByDesc('id')
                ->limit(80)
                ->get()
                ->reverse()
                ->values();
        }

        // Only update read status if there are actually incoming unread messages
        $hasIncomingUnread = $messages->contains(
            fn ($m) => (int) $m->sender_id === (int) $user->id && ! $m->is_read
        );

        if ($hasIncomingUnread || $afterId === 0) {
            Message::where('sender_id', $user->id)
                ->where('receiver_id', $authId)
                ->where('is_read', false)
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }

        return response()->json([
            'messages' => $messages->map(fn ($m) => $this->formatMessagePayload($m))->values(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar_url,
            ],
        ]);
    }
}
