<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private function getConversationsList()
    {
        return Message::where('sender_id', Auth::id())
            ->orWhere('receiver_id', Auth::id())
            ->with(['sender', 'receiver'])
            ->latest()
            ->get()
            ->groupBy(function ($message) {
                return $message->sender_id === Auth::id() 
                    ? $message->receiver_id 
                    : $message->sender_id;
            });
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
        $conversations = Message::where('sender_id', Auth::id())
            ->orWhere('receiver_id', Auth::id())
            ->with(['sender', 'receiver'])
            ->latest()
            ->get()
            ->groupBy(function ($message) {
                return $message->sender_id === Auth::id() 
                    ? $message->receiver_id 
                    : $message->sender_id;
            })
            ->take(10)
            ->map(function ($messages, $userId) {
                $lastMessage = $messages->first();
                $otherUser = $lastMessage->sender_id === Auth::id() 
                    ? $lastMessage->receiver 
                    : $lastMessage->sender;
                
                $unreadCount = Message::where('sender_id', $otherUser->id)
                    ->where('receiver_id', Auth::id())
                    ->where('is_read', false)
                    ->count();

                return [
                    'user_id' => $otherUser->id,
                    'user_name' => $otherUser->name,
                    'user_avatar' => $otherUser->avatar,
                    'last_message' => $lastMessage->message,
                    'last_message_time' => $lastMessage->created_at->toISOString(),
                    'unread_count' => $unreadCount,
                ];
            })
            ->values();

        return response()->json([
            'conversations' => $conversations,
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
