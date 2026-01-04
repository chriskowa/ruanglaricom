<?php

namespace App\Http\Controllers;

use App\Models\User;

class RunnerProfileController extends Controller
{
    public function show($username)
    {
        // Check if input is numeric (ID) or string (username)
        if (is_numeric($username)) {
            $user = User::where('id', $username)->first();

            // If not found by ID, try username just in case user has numeric username (unlikely but possible)
            if (! $user) {
                $user = User::where('username', $username)->firstOrFail();
            }
        } else {
            $user = User::where('username', $username)->firstOrFail();
        }

        // Ensure user is a runner or coach (coaches are also runners usually)
        // But maybe strictly 'runner'? The prompt says "profile detail user pada public ... lebih runner".
        // Let's just allow any user but show runner stats.

        return view('runner.profile', compact('user'));
    }
}
