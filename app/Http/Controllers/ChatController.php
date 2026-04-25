<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Services\OpenAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private function getAiCoach()
    {
        return User::firstOrCreate(
            ['email' => 'ai-coach@ruanglari.com'],
            [
                'name' => 'Coach AI',
                'password' => bcrypt(str()->random(16)),
                'role' => 'coach',
                'username' => 'coach-ai',
                'avatar' => 'images/profile/17.jpg',
                'is_active' => true,
            ]
        );
    }

    private function getConversationsList()
    {
        $aiCoach = $this->getAiCoach();

        $conversations = Message::where('sender_id', Auth::id())
            ->orWhere('receiver_id', Auth::id())
            ->with(['sender', 'receiver'])
            ->latest()
            ->get()
            ->groupBy(function ($message) {
                return $message->sender_id === Auth::id()
                    ? $message->receiver_id
                    : $message->sender_id;
            });

        // Ensure AI Coach is in the list even if no messages yet
        if (!$conversations->has($aiCoach->id)) {
            $conversations->put($aiCoach->id, collect());
        }

        return $conversations;
    }

    public function index()
    {
        $conversations = $this->getConversationsList();

        return view('chat.index', [
            'conversations' => $conversations,
        ]);
    }

    public function show(User $user)
    {
        $conversations = $this->getConversationsList();

        $messages = Message::where(function ($query) use ($user) {
            $query->where('sender_id', Auth::id())
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', Auth::id());
        })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        Message::where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return view('chat.show', [
            'user' => $user,
            'messages' => $messages,
            'conversations' => $conversations,
        ]);
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $user->id,
            'message' => $request->message,
        ]);

        $message->load(['sender', 'receiver']);

        // AI Coach Auto-reply
        if ($user->email === 'ai-coach@ruanglari.com') {
            $openAiService = app(OpenAiService::class);
            $aiResponse = $openAiService->getCoachResponse(Auth::user(), $request->message);

            if ($aiResponse) {
                Message::create([
                    'sender_id' => $user->id,
                    'receiver_id' => Auth::id(),
                    'message' => $aiResponse,
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $message->receiver_id,
                    'message' => $message->message,
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at->toISOString(),
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                        'avatar' => $message->sender->avatar,
                    ],
                ],
            ]);
        }

        return back()->with('success', 'Pesan berhasil dikirim');
    }

    public function getConversations()
    {
        $aiCoach = $this->getAiCoach();

        $conversations = Message::where('sender_id', Auth::id())
            ->orWhere('receiver_id', Auth::id())
            ->with(['sender', 'receiver'])
            ->latest()
            ->get()
            ->groupBy(function ($message) {
                return $message->sender_id === Auth::id()
                    ? $message->receiver_id
                    : $message->sender_id;
            });

        $formattedConversations = $conversations->take(10)
            ->map(function ($messages, $userId) {
                $lastMessage = $messages->first();
                if (!$lastMessage) return null;

                $otherUser = $lastMessage->sender_id === Auth::id()
                    ? $lastMessage->receiver
                    : $lastMessage->sender;

                if (!$otherUser) return null;

                $unreadCount = Message::where('sender_id', $otherUser->id)
                    ->where('receiver_id', Auth::id())
                    ->where('is_read', false)
                    ->count();

                return [
                    'user_id' => $otherUser->id,
                    'user_name' => $otherUser->name,
                    'user_avatar' => $otherUser->avatar,
                    'user_email' => $otherUser->email,
                    'last_message' => $lastMessage->message,
                    'last_message_time' => $lastMessage->created_at->toISOString(),
                    'unread_count' => $unreadCount,
                ];
            })
            ->filter()
            ->values();

        // Ensure AI Coach is in the list
        $hasAiCoach = $formattedConversations->contains('user_id', $aiCoach->id);
        if (!$hasAiCoach) {
            $formattedConversations->prepend([
                'user_id' => $aiCoach->id,
                'user_name' => $aiCoach->name,
                'user_avatar' => $aiCoach->avatar,
                'user_email' => $aiCoach->email,
                'last_message' => 'Halo! Saya Coach AI Anda. Ada yang bisa saya bantu?',
                'last_message_time' => now()->toISOString(),
                'unread_count' => 0,
            ]);
        }

        return response()->json([
            'conversations' => $formattedConversations,
        ]);
    }

    public function getMessages($userId)
    {
        $user = User::findOrFail($userId);

        $messages = Message::where(function ($query) use ($user) {
            $query->where('sender_id', Auth::id())
                ->where('receiver_id', $user->id);
        })->orWhere(function ($query) use ($user) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', Auth::id());
        })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $message->receiver_id,
                    'message' => $message->message,
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at->toISOString(),
                    'sender' => [
                        'id' => $message->sender->id,
                        'name' => $message->sender->name,
                        'avatar' => $message->sender->avatar,
                    ],
                ];
            });

        // Mark messages as read
        Message::where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'messages' => $messages,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
            ],
        ]);
    }
}
