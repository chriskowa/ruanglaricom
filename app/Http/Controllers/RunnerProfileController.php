<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class RunnerProfileController extends Controller
{
    public function show($username)
    {
        $user = User::where('username', $username)->firstOrFail();
        
        // Ensure user is a runner or coach (coaches are also runners usually)
        // But maybe strictly 'runner'? The prompt says "profile detail user pada public ... lebih runner".
        // Let's just allow any user but show runner stats.
        
        return view('runner.profile', compact('user'));
    }
}
