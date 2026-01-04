<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function follow(User $user)
    {
        if (Auth::id() === $user->id) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Anda tidak bisa follow diri sendiri.'], 422);
            }

            return back()->with('error', 'Anda tidak bisa follow diri sendiri.');
        }

        if (Auth::user()->isFollowing($user)) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Anda sudah follow user ini.'], 422);
            }

            return back()->with('error', 'Anda sudah follow user ini.');
        }

        Auth::user()->following()->attach($user->id);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Berhasil follow '.$user->name, 'status' => 'following']);
        }

        return back()->with('success', 'Berhasil follow '.$user->name);
    }

    public function unfollow(User $user)
    {
        if (! Auth::user()->isFollowing($user)) {
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Anda belum follow user ini.'], 422);
            }

            return back()->with('error', 'Anda belum follow user ini.');
        }

        Auth::user()->following()->detach($user->id);

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Berhasil unfollow '.$user->name, 'status' => 'unfollowed']);
        }

        return back()->with('success', 'Berhasil unfollow '.$user->name);
    }
}
