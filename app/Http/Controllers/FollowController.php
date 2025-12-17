<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function follow(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Anda tidak bisa follow diri sendiri.');
        }

        if (Auth::user()->isFollowing($user)) {
            return back()->with('error', 'Anda sudah follow user ini.');
        }

        Auth::user()->following()->attach($user->id);

        return back()->with('success', 'Berhasil follow ' . $user->name);
    }

    public function unfollow(User $user)
    {
        if (!Auth::user()->isFollowing($user)) {
            return back()->with('error', 'Anda belum follow user ini.');
        }

        Auth::user()->following()->detach($user->id);

        return back()->with('success', 'Berhasil unfollow ' . $user->name);
    }
}
