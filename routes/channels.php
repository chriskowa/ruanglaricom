<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\RunThread;

Broadcast::channel('thread.{threadId}', function ($user, $threadId) {
    $thread = RunThread::find($threadId);
    if (!$thread) return false;
    if ((int) $thread->creator_id === (int) $user->id) return true;
    return $thread->participants()->where('user_id', $user->id)->where('status', 'joined')->exists();
});
